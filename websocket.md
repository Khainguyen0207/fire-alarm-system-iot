# Tích Hợp WebSocket Cho Frontend

Máy chủ backend phát dữ liệu telemetry thời gian thực qua Laravel Reverb. Reverb hỗ trợ giao thức Pusher, nên frontend có thể dùng Laravel Echo/Pusher hoặc API `WebSocket` có sẵn của trình duyệt. Tài liệu này dùng WebSocket thuần và không cần cài thêm thư viện.

## Cấu Hình Local

| Giá trị | Giá trị trên máy local |
| --- | --- |
| Laravel API | `http://localhost:8000/api/v1` |
| Reverb WebSocket | `ws://localhost:8080` |
| Reverb app key | `local-reverb-key` |
| Channel của thiết bị `ESP32_001` | `private-devices.ESP32_001` |
| Tên event | `telemetry.received` |

Dùng `wss://` thay cho `ws://` khi frontend chạy qua HTTPS. Reverb app key được phép có trong cấu hình frontend. Tuyệt đối không để lộ `REVERB_APP_SECRET` trong mã frontend, file môi trường của frontend hoặc request từ trình duyệt.

## Luồng Kết Nối

```text
Frontend -- POST /auth/login --------------------> Laravel
Frontend <-- Sanctum token ----------------------- Laravel

Frontend -- WebSocket /app/{REVERB_APP_KEY} -----> Reverb
Frontend <-- pusher:connection_established ------- Reverb

Frontend -- POST /broadcasting/auth -------------> Laravel
              Bearer token + socket_id + channel
Frontend <-- chữ ký quyền đăng ký --------------- Laravel

Frontend -- pusher:subscribe --------------------> Reverb

ESP32 -- POST /iot/data --------------------------> Laravel
Laravel -- telemetry.received --------------------> Reverb
Frontend <-- telemetry.received ------------------ Reverb
```

ESP32 gửi telemetry đến Laravel bằng HTTP. Frontend không gửi telemetry qua WebSocket; frontend chỉ mở kết nối, xác thực quyền, đăng ký channel của thiết bị và nhận dữ liệu được phát đi.

## 1. Đăng Nhập Lấy Sanctum Token

```ts
const apiBaseUrl = 'http://localhost:8000/api/v1';

const response = await fetch(`${apiBaseUrl}/auth/login`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  body: JSON.stringify({
    email: 'admin@example.com',
    password: 'password',
  }),
});

const body = await response.json();

if (!response.ok) {
  throw new Error(body.message ?? 'Đăng nhập thất bại.');
}

const token: string = body.data.token;
```

Lưu token theo cách quản lý xác thực hiện có của frontend. Chỉ gửi token dưới dạng Bearer token đến Laravel API, bao gồm endpoint xác thực channel ở bước dưới.

## 2. Kết Nối Và Đăng Ký Thiết Bị

Tạo một socket cho mỗi phiên đăng nhập trên trình duyệt. Chỉ đăng ký channel sau khi Reverb gửi `pusher:connection_established`. Không gọi endpoint xác thực trước khi nhận được `socket_id`.

```ts
type ReverbFrame = {
  event: string;
  channel?: string;
  data: string | Record<string, unknown>;
};

type Telemetry = {
  deviceId: string;
  recordedAt: string;
  temperature: number | null;
  humidity: number | null;
  smokePpm: number | null;
  flameDetected: boolean | null;
  state: 'NORMAL' | 'WARNING' | 'DANGER';
  metadata: Record<string, unknown> | null;
};

export async function connectToDevice(
  token: string,
  deviceId: string,
  onTelemetry: (telemetry: Telemetry) => void,
): Promise<WebSocket> {
  const reverbUrl =
    'ws://localhost:8080/app/local-reverb-key?protocol=7&client=frontend&version=1.0&flash=false';
  const channelName = `private-devices.${deviceId}`;
  const socket = new WebSocket(reverbUrl);

  socket.addEventListener('message', async (message) => {
    const frame = JSON.parse(message.data) as ReverbFrame;
    const data = typeof frame.data === 'string' ? JSON.parse(frame.data) : frame.data;

    if (frame.event === 'pusher:connection_established') {
      const socketId = (data as { socket_id: string }).socket_id;
      const authorizationResponse = await fetch(`${apiBaseUrl}/broadcasting/auth`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/x-www-form-urlencoded',
          Accept: 'application/json',
        },
        body: new URLSearchParams({
          socket_id: socketId,
          channel_name: channelName,
        }),
      });
      const authorization = await authorizationResponse.json();

      if (!authorizationResponse.ok) {
        socket.close();
        throw new Error(authorization.message ?? 'Xác thực channel thất bại.');
      }

      socket.send(JSON.stringify({
        event: 'pusher:subscribe',
        data: {
          channel: channelName,
          ...authorization,
        },
      }));
      return;
    }

    if (frame.event === 'pusher:ping') {
      socket.send(JSON.stringify({ event: 'pusher:pong', data: {} }));
      return;
    }

    if (frame.event === 'telemetry.received') {
      onTelemetry(data as Telemetry);
    }
  });

  socket.addEventListener('error', () => {
    console.error('Reverb WebSocket gặp lỗi.');
  });

  return socket;
}
```

Với React, hãy đóng socket trong hàm cleanup khi component bị unmount, người dùng đăng xuất hoặc `deviceId` thay đổi:

```ts
socket.close();
```

## Dữ Liệu Event

Laravel phát `telemetry.received` đến private channel của thiết bị gửi telemetry. `data` trong WebSocket frame được giao thức Pusher mã hóa thành chuỗi JSON, nên cần parse trước khi đọc các trường telemetry.

```json
{
  "deviceId": "ESP32_001",
  "recordedAt": "2026-08-31T10:00:00+07:00",
  "temperature": 65,
  "humidity": 52,
  "smokePpm": 410,
  "flameDetected": true,
  "state": "DANGER",
  "metadata": null
}
```

`state` được backend tính toán và frontend phải hiển thị đúng giá trị nhận được. Các giá trị hợp lệ là `NORMAL`, `WARNING` và `DANGER`.

## Quyền Đăng Ký Channel

`POST /api/v1/broadcasting/auth` yêu cầu Sanctum Bearer token hợp lệ. Hiện tại backend cho phép mọi người dùng đã đăng nhập đăng ký mọi channel `devices.{deviceId}`. Nếu token thiếu, hết hạn hoặc không hợp lệ, endpoint trả lỗi xác thực và frontend không được gửi request subscribe.

## Xử Lý Sự Cố

| Hiện tượng | Cần kiểm tra |
| --- | --- |
| Không thể kết nối WebSocket | Kiểm tra Reverb đang chạy và cổng `8080` truy cập được. Dùng `ws://` ở local và `wss://` khi chạy sau HTTPS. |
| Nhận `401` từ `/broadcasting/auth` | Đăng nhập lại và kiểm tra header `Authorization: Bearer <token>` đã được gửi. |
| Bị từ chối xác thực channel | Kiểm tra `socket_id` lấy từ `pusher:connection_established` và channel đúng là `private-devices.{deviceId}`. |
| Đã kết nối nhưng không có telemetry | Kiểm tra request ESP32 đến `POST /api/v1/iot/data` trả `201`, đồng thời frontend đã đăng ký đúng device ID. |
| Kết nối bị đóng sau một thời gian | Phản hồi `pusher:ping` bằng `pusher:pong` như đoạn mã ở trên. |

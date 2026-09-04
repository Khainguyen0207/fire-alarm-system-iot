<!DOCTYPE html>
<html lang="vi">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Reverb WebSocket Lab</title>
        <style>
            :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; background: #07111d; color: #e8f0fa; }
            * { box-sizing: border-box; }
            body { margin: 0; min-width: 320px; background: radial-gradient(circle at 80% -20%, #144d61 0, transparent 32rem), #07111d; }
            button, input { font: inherit; }
            button { cursor: pointer; }
            code, pre { font-family: "SFMono-Regular", Consolas, monospace; }
            .shell { width: min(1180px, calc(100% - 32px)); margin: auto; padding: 40px 0 72px; }
            .hero, .panel, .metric { border: 1px solid #264057; background: #0c1a2a; box-shadow: 0 18px 48px #0004; }
            .hero { display: flex; justify-content: space-between; gap: 24px; padding: 30px; border-radius: 22px; }
            .eyebrow { color: #6ee7c1; font-size: .76rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
            h1 { max-width: 760px; margin: 8px 0 12px; font-size: clamp(2.2rem, 6vw, 4.4rem); line-height: 1; letter-spacing: -.06em; }
            h2 { margin: 0; font-size: 1.1rem; }
            h3 { margin: 0; font-size: .9rem; color: #b9cbe0; }
            p { margin: 0; color: #aabdd2; line-height: 1.6; }
            .status { display: grid; place-items: center; min-width: 150px; padding: 20px; border: 1px solid #33536b; border-radius: 16px; text-align: center; }
            .lamp { width: 12px; height: 12px; margin: 0 auto 9px; border-radius: 999px; background: #63758a; box-shadow: 0 0 0 5px #63758a22; }
            .status[data-state="connected"] .lamp { background: #56e0ad; box-shadow: 0 0 0 5px #56e0ad22, 0 0 24px #56e0ad; }
            .status[data-state="error"] .lamp { background: #ff6f75; box-shadow: 0 0 0 5px #ff6f7522; }
            .status strong { display: block; font-size: .9rem; }
            .status small { color: #8ca2ba; }
            .grid { display: grid; grid-template-columns: 390px minmax(0, 1fr); gap: 18px; margin-top: 18px; }
            .panel { padding: 20px; border-radius: 18px; }
            .stack { display: grid; gap: 14px; }
            label { display: grid; gap: 6px; color: #b9cbe0; font-size: .82rem; font-weight: 700; }
            input { width: 100%; padding: 10px 11px; border: 1px solid #35516b; border-radius: 9px; outline: none; background: #081421; color: #eff7ff; }
            input:focus { border-color: #68d7ea; box-shadow: 0 0 0 3px #68d7ea22; }
            .two { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
            .actions { display: flex; flex-wrap: wrap; gap: 9px; }
            button { padding: 10px 13px; border: 1px solid #36536c; border-radius: 9px; background: #13283a; color: #e8f0fa; font-weight: 750; }
            button.primary { border-color: #6ee7c1; background: #1c8d75; color: #041812; }
            button.danger { color: #ffadb0; }
            .hint { padding: 12px; border-left: 3px solid #57cce3; background: #0b2432; color: #b7d0df; font-size: .86rem; line-height: 1.55; }
            .metrics { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 16px 0; }
            .metric { padding: 14px; border-radius: 12px; }
            .metric span { display: block; color: #91a9c0; font-size: .75rem; font-weight: 750; text-transform: uppercase; }
            .metric strong { display: block; margin-top: 5px; font-size: 1.3rem; }
            .metric.state-normal strong { color: #6ee7c1; }
            .metric.state-warning strong { color: #ffc56a; }
            .metric.state-danger strong { color: #ff7780; }
            pre { min-height: 170px; max-height: 310px; margin: 12px 0 0; overflow: auto; padding: 14px; border: 1px solid #263f57; border-radius: 10px; background: #06101b; color: #b8d4ec; font-size: .78rem; line-height: 1.55; white-space: pre-wrap; word-break: break-word; }
            .guide { margin-top: 18px; }
            .steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 14px; }
            .step { padding: 15px; border-top: 2px solid #3e8dac; background: #0b1928; }
            .step b { display: block; margin-bottom: 6px; color: #77dcf0; }
            .step p { font-size: .83rem; }
            .mono { color: #7ee6c1; }
            @media (max-width: 800px) { .hero, .grid { grid-template-columns: 1fr; display: grid; } .status { justify-self: start; } .metrics, .steps { grid-template-columns: repeat(2, 1fr); } }
            @media (max-width: 480px) { .shell { width: min(100% - 22px, 1180px); padding-top: 12px; } .hero, .panel { padding: 16px; } .two, .metrics, .steps { grid-template-columns: 1fr; } }
        </style>
    </head>
    <body>
        <main class="shell">
            <section class="hero">
                <div>
                    <div class="eyebrow">Local-only Reverb laboratory</div>
                    <h1>Nhìn thấy WebSocket hoạt động từng bước.</h1>
                    <p>Trang này không dùng Echo. Nó tự thực hiện Pusher protocol mà Reverb hỗ trợ: mở WebSocket, lấy <code>socket_id</code>, xác thực private channel bằng Sanctum rồi nhận telemetry thời gian thực.</p>
                </div>
                <div class="status" id="status" data-state="idle">
                    <div><i class="lamp"></i><strong id="status-title">Chưa kết nối</strong><small id="status-detail">Sẵn sàng cấu hình</small></div>
                </div>
            </section>

            <section class="grid">
                <aside class="panel stack">
                    <h2>1. Thông tin kết nối</h2>
                    <label>Laravel API URL<input id="api-url" value="http://localhost:8000"></label>
                    <div class="two">
                        <label>Reverb host<input id="reverb-host" value="localhost"></label>
                        <label>Reverb port<input id="reverb-port" value="8080"></label>
                    </div>
                    <label>Reverb app key<input id="reverb-key" value="local-reverb-key"></label>
                    <label>Device ID<input id="device-id" value="ESP32_001"></label>
                    <h2>2. Lấy Sanctum token</h2>
                    <label>Email<input id="email" value="admin@example.com" type="email"></label>
                    <label>Mật khẩu<input id="password" value="password" type="password"></label>
                    <div class="actions"><button id="login" type="button">Đăng nhập lấy token</button></div>
                    <label>Bearer token<input id="token" placeholder="Dán token hoặc dùng nút đăng nhập"></label>
                    <div class="actions">
                        <button class="primary" id="connect" type="button">Kết nối private channel</button>
                        <button class="danger" id="disconnect" type="button">Ngắt kết nối</button>
                        <button id="clear" type="button">Xóa log</button>
                    </div>
                    <p class="hint">Luồng chuẩn: <span class="mono">POST /auth/login</span> → WebSocket <span class="mono">connection_established</span> → <span class="mono">POST /broadcasting/auth</span> → subscribe <span class="mono">private-devices.{id}</span>.</p>
                </aside>

                <section class="panel">
                    <h2>Telemetry nhận gần nhất</h2>
                    <div class="metrics" id="metrics">
                        <div class="metric"><span>Nhiệt độ</span><strong id="temperature">--</strong></div>
                        <div class="metric"><span>Độ ẩm</span><strong id="humidity">--</strong></div>
                        <div class="metric"><span>Khói</span><strong id="smoke">--</strong></div>
                        <div class="metric" id="state-card"><span>Trạng thái</span><strong id="fire-state">--</strong></div>
                    </div>
                    <h3>Payload event <code>telemetry.received</code></h3>
                    <pre id="payload">Chưa có event.</pre>
                    <h3 style="margin-top:18px">Nhật ký protocol</h3>
                    <pre id="log">Chờ thao tác...</pre>
                </section>
            </section>

            <section class="panel guide">
                <h2>Giải thích nhanh</h2>
                <div class="steps">
                    <article class="step"><b>01. WebSocket</b><p>Kênh TCP hai chiều giữ mở. Server có thể đẩy event ngay, không cần trình duyệt liên tục gọi API.</p></article>
                    <article class="step"><b>02. socket_id</b><p>Reverb trả ID riêng khi handshake xong. ID này chứng minh yêu cầu xác thực thuộc đúng kết nối.</p></article>
                    <article class="step"><b>03. Private channel</b><p>Trình duyệt gửi token Sanctum đến API. Laravel kiểm tra token và trả chữ ký subscribe, không đưa Reverb secret ra client.</p></article>
                    <article class="step"><b>04. Event</b><p>ESP32 gửi telemetry → Laravel lưu DB → broadcast <code>telemetry.received</code> đến channel của device.</p></article>
                </div>
            </section>
        </main>

        <script>
            const elements = Object.fromEntries(['api-url', 'reverb-host', 'reverb-port', 'reverb-key', 'device-id', 'email', 'password', 'token', 'status', 'status-title', 'status-detail', 'payload', 'log', 'temperature', 'humidity', 'smoke', 'fire-state', 'state-card'].map((id) => [id, document.getElementById(id)]));
            let socket;
            let socketId;

            function log(message, data) {
                const stamp = new Date().toLocaleTimeString('vi-VN');
                const suffix = data === undefined ? '' : `\n${JSON.stringify(data, null, 2)}`;
                elements.log.textContent = `[${stamp}] ${message}${suffix}\n\n${elements.log.textContent}`;
            }

            function setStatus(state, title, detail) {
                elements.status.dataset.state = state;
                elements['status-title'].textContent = title;
                elements['status-detail'].textContent = detail;
            }

            function apiUrl(path) {
                return `${elements['api-url'].value.replace(/\/$/, '')}${path}`;
            }

            async function login() {
                try {
                    log('Gọi API đăng nhập.');
                    const response = await fetch(apiUrl('/api/v1/auth/login'), { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify({ email: elements.email.value, password: elements.password.value }) });
                    const body = await response.json();
                    if (!response.ok) throw new Error(body.message || 'Đăng nhập thất bại.');
                    elements.token.value = body.data?.token || body.token || '';
                    if (!elements.token.value) throw new Error('API không trả token.');
                    log('Đã nhận Sanctum token.');
                } catch (error) { log(`Lỗi đăng nhập: ${error.message}`); }
            }

            async function subscribe() {
                const channelName = `private-devices.${elements['device-id'].value}`;
                const response = await fetch(apiUrl('/api/v1/broadcasting/auth'), { method: 'POST', headers: { Authorization: `Bearer ${elements.token.value}`, 'Content-Type': 'application/x-www-form-urlencoded', Accept: 'application/json' }, body: new URLSearchParams({ socket_id: socketId, channel_name: channelName }) });
                const auth = await response.json();
                if (!response.ok) throw new Error(auth.message || 'Private channel bị từ chối.');
                socket.send(JSON.stringify({ event: 'pusher:subscribe', data: { channel: channelName, ...auth } }));
                log(`Đã gửi yêu cầu subscribe ${channelName}.`, auth);
            }

            function showTelemetry(data) {
                elements.payload.textContent = JSON.stringify(data, null, 2);
                elements.temperature.textContent = data.temperature == null ? '--' : `${data.temperature} °C`;
                elements.humidity.textContent = data.humidity == null ? '--' : `${data.humidity} %`;
                elements.smoke.textContent = data.smokePpm == null ? '--' : `${data.smokePpm} ppm`;
                const state = data.state || '--';
                elements['fire-state'].textContent = state;
                elements['state-card'].className = `metric state-${state.toLowerCase()}`;
            }

            function connect() {
                if (!elements.token.value) { log('Cần Sanctum token trước khi kết nối.'); return; }
                socket?.close();
                const scheme = location.protocol === 'https:' ? 'wss' : 'ws';
                const url = `${scheme}://${elements['reverb-host'].value}:${elements['reverb-port'].value}/app/${elements['reverb-key'].value}?protocol=7&client=websocket-lab&version=1.0&flash=false`;
                setStatus('idle', 'Đang kết nối', 'Chờ handshake');
                log(`Mở WebSocket: ${url}`);
                socket = new WebSocket(url);
                socket.onmessage = async ({ data }) => {
                    const frame = JSON.parse(data);
                    const payload = typeof frame.data === 'string' ? JSON.parse(frame.data) : frame.data;
                    log(`Nhận frame ${frame.event}.`, payload);
                    if (frame.event === 'pusher:connection_established') { socketId = payload.socket_id; setStatus('connected', 'Đã kết nối', `socket_id: ${socketId}`); try { await subscribe(); } catch (error) { setStatus('error', 'Lỗi xác thực', error.message); log(error.message); } }
                    if (frame.event === 'pusher:ping') socket.send(JSON.stringify({ event: 'pusher:pong', data: {} }));
                    if (frame.event === 'telemetry.received') showTelemetry(payload);
                };
                socket.onerror = () => { setStatus('error', 'Lỗi WebSocket', 'Kiểm tra Reverb host/port'); log('WebSocket báo lỗi.'); };
                socket.onclose = () => { if (elements.status.dataset.state !== 'error') setStatus('idle', 'Đã ngắt', 'Có thể kết nối lại'); log('WebSocket đã đóng.'); };
            }

            document.getElementById('login').addEventListener('click', login);
            document.getElementById('connect').addEventListener('click', connect);
            document.getElementById('disconnect').addEventListener('click', () => socket?.close());
            document.getElementById('clear').addEventListener('click', () => { elements.log.textContent = ''; });
        </script>
    </body>
</html>

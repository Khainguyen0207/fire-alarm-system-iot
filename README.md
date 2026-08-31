# Fire Alarm System IoT

Laravel backend for a single ESP32 fire monitor. The ESP32 submits telemetry, Laravel validates and stores it in MySQL, computes the authoritative fire state, and broadcasts it to authenticated clients through Laravel Reverb.

## Start

```sh
./scripts/setup.sh
```

The idempotent script builds the `app`, `mysql`, and `reverb` services, then applies and seeds migrations. The API is available at `http://localhost:8000`, MySQL at port `3307`, and Reverb at port `8080`.

Development credentials:

- Admin email: `admin@example.com`
- Admin password: `password`
- Device ID: `ESP32_001`
- Device API key: `development-device-key`

Use `docker compose logs -f app reverb` to observe the application and WebSocket server. Stop the stack with `docker compose down`; add `-v` to reset MySQL data.

## Run Without Docker

Configure MySQL and Reverb values in `backend/.env`, then install dependencies and apply the seeded database:

```sh
cd backend
composer install
npm install --ignore-scripts
php artisan migrate --seed
npm run build
```

Run Laravel and Reverb in separate terminals:

```sh
cd backend
php artisan serve
```

```sh
cd backend
php artisan reverb:start --host=0.0.0.0 --port=8080
```

Open `http://localhost:8000`, sign in with the development admin credentials, and select device `ESP32_001`. The WebSocket demo authenticates the private channel with the generated Sanctum token and then listens for `telemetry.received` events.

To manually trigger a WebSocket event, send a valid telemetry payload from a third terminal:

```sh
curl -X POST http://localhost:8000/api/v1/iot/data \
  -H "Content-Type: application/json" \
  -H "X-Device-Key: development-device-key" \
  -d '{
    "deviceId": "ESP32_001",
    "recordedAt": "2026-08-31T10:00:00+07:00",
    "temperature": 65,
    "humidity": 52,
    "smokePpm": 410,
    "flameDetected": true
  }'
```

When the request returns `201`, the telemetry and event JSON should appear on the demo page without reloading. If the connection fails, make sure Reverb is listening on `ws://localhost:8080` and that the host and port on the page match the local configuration.

## Architecture

`ESP32 -> POST /api/v1/iot/data -> Laravel services -> MySQL -> Reverb private channel -> frontend`

The backend, not the ESP32, decides the fire state:

- `DANGER`: temperature >= danger threshold, smoke >= danger threshold, and flame detected.
- `WARNING`: temperature >= warning threshold or smoke >= warning threshold.
- `NORMAL`: otherwise.

Timestamps are stored in UTC and returned as ISO-8601 in `Asia/Ho_Chi_Minh`. Raw incoming JSON is retained on every sensor measurement. Closed minute buckets are idempotently aggregated every five minutes.

## API

All responses use `success`, `data`, `message`, and `errors`. Admin endpoints require `Authorization: Bearer <Sanctum token>`.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| POST | `/api/v1/auth/login` | Obtain a Sanctum token |
| POST | `/api/v1/auth/logout` | Revoke the active token |
| POST | `/api/v1/iot/data` | Ingest ESP32 telemetry using `X-Device-Key` |
| GET/POST | `/api/v1/devices` | List or create devices |
| GET/PATCH/DELETE | `/api/v1/devices/{device}` | Manage a device |
| GET/POST | `/api/v1/devices/{device}/sensors` | List or create device sensors |
| PATCH/DELETE | `/api/v1/sensors/{sensor}` | Manage a sensor |
| GET | `/api/v1/devices/{device}/measurements/latest` | Latest combined telemetry |
| GET | `/api/v1/devices/{device}/measurements` | Raw history; filters: `from`, `to`, `sensorType`, `perPage` |
| GET | `/api/v1/devices/{device}/measurements/minutes` | Minute history; same filters |
| GET/PATCH | `/api/v1/settings`, `/api/v1/settings/{setting}` | Read thresholds; only values can change |

Reverb emits `telemetry.received` on the private `devices.{deviceId}` channel. Every authenticated user is authorized for every device channel.

Import `postman/fire-alarm-system.postman_collection.json` into Postman for ready-made requests.

## Development

```sh
cd backend
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

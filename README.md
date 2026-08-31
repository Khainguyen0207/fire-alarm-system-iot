# Fire Alarm System IoT

Laravel backend for a single ESP32 fire monitor. The ESP32 submits telemetry, Laravel validates and stores it in MySQL, computes the authoritative fire state, and broadcasts it to authenticated clients through Laravel Reverb.

## Start

```sh
./scripts/setup.sh
```

The idempotent script builds the `app`, `mysql`, and `reverb` services, generates an application key, and applies/seeds migrations. The API is then available at `http://localhost:8000` and Reverb at port `8080`.

Development credentials:

- Admin email: `admin@example.com`
- Admin password: `password`
- Device ID: `ESP32_001`
- Device API key: `development-device-key`

Use `docker compose logs -f app reverb` to observe the application and WebSocket server. Stop the stack with `docker compose down`; add `-v` to reset MySQL data.

## Architecture

`ESP32 -> POST /api/iot/data -> Laravel services -> MySQL -> Reverb private channel -> frontend`

The backend, not the ESP32, decides the fire state:

- `DANGER`: temperature >= danger threshold, smoke >= danger threshold, and flame detected.
- `WARNING`: temperature >= warning threshold or smoke >= warning threshold.
- `NORMAL`: otherwise.

Timestamps are stored in UTC and returned as ISO-8601 in `Asia/Ho_Chi_Minh`. Raw incoming JSON is retained on every sensor measurement. Closed minute buckets are idempotently aggregated every five minutes.

## API

All responses use `success`, `data`, `message`, and `errors`. Admin endpoints require `Authorization: Bearer <Sanctum token>`.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| POST | `/api/auth/login` | Obtain a Sanctum token |
| POST | `/api/auth/logout` | Revoke the active token |
| POST | `/api/iot/data` | Ingest ESP32 telemetry using `X-Device-Key` |
| GET/POST | `/api/devices` | List or create devices |
| GET/PATCH/DELETE | `/api/devices/{device}` | Manage a device |
| GET/POST | `/api/devices/{device}/sensors` | List or create device sensors |
| PATCH/DELETE | `/api/sensors/{sensor}` | Manage a sensor |
| GET | `/api/devices/{device}/measurements/latest` | Latest combined telemetry |
| GET | `/api/devices/{device}/measurements` | Raw history; filters: `from`, `to`, `sensorType`, `perPage` |
| GET | `/api/devices/{device}/measurements/minutes` | Minute history; same filters |
| GET/PATCH | `/api/settings`, `/api/settings/{setting}` | Read thresholds; only values can change |

Reverb emits `telemetry.received` on the private `devices.{deviceId}` channel. Every authenticated user is authorized for every device channel.

Import `postman/fire-alarm-system.postman_collection.json` into Postman for ready-made requests.

## Development

```sh
cd backend
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

To run without Docker, configure MySQL and Reverb values in `backend/.env`, run `php artisan migrate --seed`, then run `php artisan serve`, `php artisan schedule:work`, and `php artisan reverb:start` in separate terminals.

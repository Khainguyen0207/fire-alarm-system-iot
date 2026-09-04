<!DOCTYPE html>
<html lang="vi">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>API Playground | Fire Alarm System</title>
        <style>
            :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, sans-serif; color: #183044; background: #f4f8fb; }
            * { box-sizing: border-box; }
            body { margin: 0; min-width: 320px; background: radial-gradient(circle at 85% -15%, #cceef5 0, transparent 34rem), #f4f8fb; }
            button, input, textarea { font: inherit; } button { cursor: pointer; } code, pre, textarea { font-family: "SFMono-Regular", Consolas, monospace; } code { color: #08765d; }
            .shell { width: min(1180px, calc(100% - 32px)); margin: auto; padding: 28px 0 72px; }
            .panel, .endpoint { border: 1px solid #cadce8; background: #fff; box-shadow: 0 18px 48px #47789818; }
            .panel { padding: 22px; border-radius: 18px; } .endpoint { padding: 16px; border-radius: 13px; box-shadow: none; }
            h1 { margin: 0; font-size: clamp(1.8rem, 4vw, 2.8rem); letter-spacing: -.05em; } h2 { margin: 0; font-size: 1.2rem; } h3 { margin: 0; font-size: 1rem; }
            p { color: #526b7e; line-height: 1.65; } .intro { margin: 8px 0 0; }
            .top { display: grid; grid-template-columns: minmax(0, 1fr) 310px; gap: 18px; } .stack { display: grid; gap: 14px; } .two { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
            label { display: grid; gap: 6px; color: #36526a; font-size: .8rem; font-weight: 750; } input, textarea { width: 100%; padding: 10px 11px; border: 1px solid #bfd1df; border-radius: 8px; outline: none; color: #183044; background: #f8fbfd; } textarea { min-height: 112px; resize: vertical; font-size: .78rem; line-height: 1.5; } input:focus, textarea:focus { border-color: #258aa3; box-shadow: 0 0 0 3px #258aa322; }
            .actions, .endpoint-head, .code-head { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; } .actions { margin-top: 12px; } .endpoint-head { justify-content: space-between; gap: 12px; } .code-head { justify-content: space-between; margin-top: 14px; }
            button { padding: 9px 12px; border: 1px solid #b7cad8; border-radius: 8px; background: #fff; color: #36526a; font-weight: 750; } button:hover { background: #edf8fb; } button.primary { border-color: #08765d; background: #08765d; color: #fff; } button.danger { color: #a94149; }
            .badge { display: inline-block; padding: 3px 6px; border-radius: 5px; font-size: .72rem; font-weight: 850; } .get { color: #106246; background: #ddf6e9; } .post { color: #13547f; background: #dff1ff; } .patch { color: #7a4f07; background: #fff0ce; } .delete { color: #942e39; background: #ffe4e7; }
            .hint, .status { padding: 11px 12px; border-left: 3px solid #258aa3; background: #edf8fb; color: #36526a; font-size: .85rem; line-height: 1.55; } .status { border-left-color: #08765d; } .status.error { border-left-color: #b94750; background: #fff0f1; color: #8b3038; }
            .section-title { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; margin-top: 24px; } .section-title p { margin: 0; font-size: .85rem; } .endpoints { display: grid; gap: 12px; margin-top: 14px; }
            .meta { margin: 8px 0 0; font-size: .84rem; } .auth { color: #08765d; font-weight: 750; } .public { color: #6d7280; font-weight: 750; }
            pre { min-height: 88px; margin: 0; overflow: auto; padding: 12px; border: 1px solid #c9d9e5; border-radius: 8px; background: #f8fbfd; color: #26465e; font-size: .78rem; line-height: 1.55; white-space: pre-wrap; word-break: break-word; }
            .hidden { display: none; } .ws-log { min-height: 180px; } .small { color: #637b8e; font-size: .8rem; }
            .layout { display: grid; grid-template-columns: 210px minmax(0, 1fr); gap: 18px; } .navigation { position: sticky; top: 18px; align-self: start; padding: 14px; border: 1px solid #cadce8; border-radius: 14px; background: #fff; } .navigation b { display: block; margin-bottom: 7px; color: #36526a; font-size: .75rem; letter-spacing: .08em; text-transform: uppercase; } .navigation a { display: block; padding: 8px; border-radius: 7px; color: #526b7e; font-size: .86rem; font-weight: 700; text-decoration: none; } .navigation a:hover { background: #e8f5f4; color: #08765d; }
            .content { display: grid; gap: 18px; } .section-title { margin-top: 0; } .group-intro { margin: 8px 0 0; } .write-warning { margin-top: 10px; padding: 9px 11px; border-left: 3px solid #bd7e18; background: #fff8e9; color: #785216; font-size: .82rem; line-height: 1.5; }
            @media (max-width: 800px) { .top, .layout { grid-template-columns: 1fr; } .navigation { position: static; display: flex; flex-wrap: wrap; gap: 4px; } .navigation b { width: 100%; } .navigation a { background: #f8fbfd; } } @media (max-width: 540px) { .shell { width: min(100% - 22px, 1180px); padding-top: 12px; } .panel { padding: 16px; } .two { grid-template-columns: 1fr; } .endpoint-head { align-items: start; flex-direction: column; } }
        </style>
    </head>
    <body>
        <main class="shell stack">
            <section class="panel top">
                <div>
                    <h1>API Playground</h1>
                    <p class="intro">Gọi API thật, xem JSON backend trả về và copy request/response. Không có dữ liệu mẫu viết tay trong các response.</p>
                    <p class="hint">Mặc định theo Docker và <code>.env.example</code>: API <code>localhost:8000</code>, Reverb <code>localhost:8080</code>, app key <code>local-reverb-key</code>. Hãy đăng nhập trước khi gọi endpoint có nhãn <span class="auth">Bearer token</span>.</p>
                </div>
                <div class="stack">
                    <label>Base API URL<input id="api-base" value="http://localhost:8000/api/v1"></label>
                    <label>Bearer token<input id="token" placeholder="Đăng nhập hoặc dán token"></label>
                    <div class="actions"><button id="save-token" type="button">Lưu token</button><button class="danger" id="clear-token" type="button">Xóa token</button></div>
                    <div class="status" id="token-status">Chưa có token. Đăng nhập ở endpoint đầu tiên hoặc dán token.</div>
                </div>
            </section>

            <section class="layout">
                <nav class="navigation" aria-label="Điều hướng tài liệu API">
                    <b>Điều hướng</b><a href="#auth">1. Xác thực</a><a href="#settings">2. Cài đặt ngưỡng</a><a href="#devices">3. Thiết bị</a><a href="#sensors">4. Cảm biến</a><a href="#measurements">5. Dữ liệu đo</a><a href="#iot">6. ESP32 / IoT</a><a href="#websocket">7. WebSocket</a>
                </nav>
                <div class="content">
                    <section class="panel" id="auth"><div class="section-title"><h2>1. Xác thực</h2><p>Gọi login trước.</p></div><p class="group-intro">Login trả token Sanctum và tự lưu vào localStorage. Dán token ở đầu trang nếu bạn đã có token. Logout sẽ thu hồi token đang dùng.</p><div class="endpoints" id="group-auth"></div></section>
                    <section class="panel" id="settings"><div class="section-title"><h2>2. Cài đặt ngưỡng</h2><p>Bearer token.</p></div><p class="group-intro">Dùng để đọc và thay đổi các ngưỡng cảnh báo khói, nhiệt độ. Thay <code>/settings/1</code> bằng ID thực tế từ response GET.</p><div class="endpoints" id="group-settings"></div></section>
                    <section class="panel" id="devices"><div class="section-title"><h2>3. Thiết bị</h2><p>Bearer token.</p></div><p class="group-intro">Tạo hoặc quản lý device. Thay <code>ESP32_001</code> trong path bằng ID device thật trước khi gọi GET, PATCH, DELETE.</p><div class="endpoints" id="group-devices"></div></section>
                    <section class="panel" id="sensors"><div class="section-title"><h2>4. Cảm biến</h2><p>Bearer token.</p></div><p class="group-intro">Sensor thuộc một device. Tạo sensor với type: <code>temperature</code>, <code>humidity</code>, <code>smoke</code> hoặc <code>flame</code>.</p><div class="endpoints" id="group-sensors"></div></section>
                    <section class="panel" id="measurements"><div class="section-title"><h2>5. Dữ liệu đo</h2><p>Bearer token.</p></div><p class="group-intro">Các endpoint chỉ đọc. Có thể thêm query <code>from</code>, <code>to</code>, <code>sensorType</code>, <code>perPage</code> vào ô Path / query.</p><div class="endpoints" id="group-measurements"></div></section>
                    <section class="panel" id="iot"><div class="section-title"><h2>6. ESP32 / IoT</h2><p>X-Device-Key.</p></div><p class="group-intro">Firmware ESP32 gửi telemetry đến endpoint này. Không gửi Bearer token; nhập API key của device vào trường <code>X-Device-Key</code>.</p><div class="endpoints" id="group-iot"></div></section>
                    <section class="panel" id="websocket">
                        <div class="section-title"><h2>7. WebSocket</h2><p>Bearer token và device ID hợp lệ.</p></div><p class="group-intro">Luồng: login → mở Reverb → nhận socket_id → backend xác thực private channel → nhận event telemetry. Endpoint broadcasting auth bên dưới chỉ dùng khi đã có socket_id.</p>
                        <div class="endpoints" id="group-websocket"></div>
                        <div class="two" style="margin-top:14px"><label>Reverb URL<input id="reverb-url" value="ws://localhost:8080"></label><label>Reverb app key<input id="reverb-key" value="local-reverb-key"></label><label>Device ID<input id="ws-device-id" value="ESP32_001"></label><label>Trạng thái<input id="ws-status" value="Chưa kết nối" readonly></label></div>
                        <div class="actions"><button class="primary" id="connect-websocket" type="button">Kết nối và subscribe</button><button id="disconnect-websocket" type="button">Ngắt kết nối</button></div>
                        <div class="code-head"><h3>WebSocket frames và telemetry thực tế</h3><button data-copy-target="ws-log" type="button">Copy</button></div><pre class="ws-log" id="ws-log">Chưa kết nối.</pre>
                    </section>
                </div>
            </section>
        </main>

        <template id="endpoint-template">
            <article class="endpoint">
                <div class="endpoint-head"><div class="actions"><span class="badge"></span><code class="url-preview"></code></div><button class="send primary" type="button">Gửi request</button></div>
                <p class="meta"><span class="access"></span> <span class="description"></span></p>
                <div class="two fields" style="margin-top:14px"></div>
                <div class="body-wrap"><div class="code-head"><h3>Request JSON</h3><button class="copy-request" type="button">Copy</button></div><textarea class="request-body" spellcheck="false"></textarea></div>
                <div class="code-head"><h3>Response JSON thực tế</h3><button class="copy-response" type="button">Copy</button></div><pre class="response">Chưa gọi API.</pre>
            </article>
        </template>

        <script>
            const storageKey = 'fire-alarm-api-token';
            const elements = {
                apiBase: document.getElementById('api-base'), token: document.getElementById('token'), tokenStatus: document.getElementById('token-status'), template: document.getElementById('endpoint-template'), wsLog: document.getElementById('ws-log'), wsStatus: document.getElementById('ws-status'),
            };
            let socket;
            let socketId;

            const definitions = [
                { category: 'auth', method: 'POST', path: '/auth/login', description: 'Lấy Sanctum token và tự lưu vào localStorage.', body: { email: 'admin@example.com', password: 'password' } },
                { category: 'auth', method: 'POST', path: '/auth/logout', auth: true, description: 'Thu hồi token đang dùng.' },
                { category: 'settings', method: 'GET', path: '/settings', auth: true, description: 'Lấy các ngưỡng cảnh báo thực tế.' },
                { category: 'settings', method: 'PATCH', path: '/settings/1', auth: true, description: 'Cập nhật value của một setting.', body: { value: '41' } },
                { category: 'devices', method: 'GET', path: '/devices', auth: true, description: 'Lấy danh sách thiết bị thực tế.' },
                { category: 'devices', method: 'POST', path: '/devices', auth: true, description: 'Tạo thiết bị.', body: { id: 'ESP32_002', name: 'Garage Monitor', status: 'active', apiKey: 'change-this-device-key', installedAt: '2026-08-31T10:00:00+07:00' } },
                { category: 'devices', method: 'GET', path: '/devices/ESP32_001', auth: true, description: 'Lấy một thiết bị và sensors.' },
                { category: 'devices', method: 'PATCH', path: '/devices/ESP32_001', auth: true, description: 'Cập nhật thiết bị.', body: { name: 'Garage Monitor', status: 'active' } },
                { category: 'devices', method: 'DELETE', path: '/devices/ESP32_001', auth: true, description: 'Xóa thiết bị.' },
                { category: 'sensors', method: 'GET', path: '/devices/ESP32_001/sensors', auth: true, description: 'Lấy cảm biến của thiết bị.' },
                { category: 'sensors', method: 'POST', path: '/devices/ESP32_001/sensors', auth: true, description: 'Tạo cảm biến.', body: { name: 'Temperature Sensor', type: 'temperature', unit: 'C', status: 'active' } },
                { category: 'sensors', method: 'PATCH', path: '/sensors/1', auth: true, description: 'Cập nhật cảm biến.', body: { name: 'Temperature Sensor', unit: 'C', status: 'active' } },
                { category: 'sensors', method: 'DELETE', path: '/sensors/1', auth: true, description: 'Xóa cảm biến.' },
                { category: 'measurements', method: 'GET', path: '/devices/ESP32_001/measurements/latest', auth: true, description: 'Lấy telemetry mới nhất.' },
                { category: 'measurements', method: 'GET', path: '/devices/ESP32_001/measurements?perPage=50', auth: true, description: 'Lấy measurements có phân trang.' },
                { category: 'measurements', method: 'GET', path: '/devices/ESP32_001/measurements/minutes?perPage=50', auth: true, description: 'Lấy measurements theo phút.' },
                { category: 'iot', method: 'POST', path: '/iot/data', deviceKey: true, description: 'ESP32 gửi telemetry. Dùng X-Device-Key, không dùng Bearer token.', body: { deviceId: 'ESP32_001', recordedAt: '2026-08-31T10:00:00+07:00', temperature: 32, humidity: 60, smokePpm: 12, flameDetected: false } },
                { category: 'websocket', method: 'POST', path: '/broadcasting/auth', auth: true, form: true, description: 'Xác thực private WebSocket channel. socket_id phải lấy từ Reverb.', body: { socket_id: '1234.5678', channel_name: 'private-devices.ESP32_001' } },
            ];

            function apiUrl(path) { return `${elements.apiBase.value.replace(/\/$/, '')}${path}`; }
            function json(value) { return JSON.stringify(value, null, 2); }
            function setToken(token) { elements.token.value = token; if (token) localStorage.setItem(storageKey, token); else localStorage.removeItem(storageKey); elements.tokenStatus.textContent = token ? 'Token đã được lưu trong localStorage.' : 'Chưa có token. Đăng nhập ở endpoint đầu tiên hoặc dán token.'; }
            function writeResponse(element, value) { element.textContent = typeof value === 'string' ? value : json(value); }
            function badgeClass(method) { return method.toLowerCase(); }

            function createField(labelText, className, value, type = 'text') {
                const label = document.createElement('label'); label.textContent = labelText;
                const input = document.createElement('input'); input.className = className; input.type = type; input.value = value; label.append(input); return label;
            }

            function renderEndpoint(definition, target) {
                const fragment = elements.template.content.cloneNode(true);
                const card = fragment.querySelector('.endpoint');
                const badge = fragment.querySelector('.badge'); badge.classList.add(badgeClass(definition.method)); badge.textContent = definition.method;
                fragment.querySelector('.url-preview').textContent = `/api/v1${definition.path}`;
                fragment.querySelector('.description').textContent = definition.description;
                const access = fragment.querySelector('.access'); access.textContent = definition.auth ? 'Bearer token.' : definition.deviceKey ? 'X-Device-Key.' : 'Public endpoint.'; access.className = definition.auth || definition.deviceKey ? 'auth' : 'public';
                const fields = fragment.querySelector('.fields'); fields.append(createField('Path / query', 'request-path', definition.path));
                if (definition.deviceKey) fields.append(createField('X-Device-Key', 'device-key', 'fire-alarm-device-key'));
                if (['POST', 'PATCH', 'DELETE'].includes(definition.method) && definition.path !== '/auth/login') { const warning = document.createElement('p'); warning.className = 'write-warning'; warning.textContent = 'Thao tác này có thể tạo, thay đổi hoặc xóa dữ liệu thật. Kiểm tra path và JSON trước khi gửi.'; fragment.querySelector('.meta').after(warning); }
                const bodyWrap = fragment.querySelector('.body-wrap'); const requestBody = fragment.querySelector('.request-body');
                if (definition.body) requestBody.value = json(definition.body); else bodyWrap.remove();
                const response = fragment.querySelector('.response');
                fragment.querySelector('.copy-request')?.addEventListener('click', () => copyText(requestBody.value, fragment.querySelector('.copy-request')));
                fragment.querySelector('.copy-response').addEventListener('click', () => copyText(response.textContent, fragment.querySelector('.copy-response')));
                fragment.querySelector('.send').addEventListener('click', () => sendRequest(definition, card));
                target.append(fragment);
            }

            async function sendRequest(definition, card) {
                const responseOutput = card.querySelector('.response'); const button = card.querySelector('.send'); const path = card.querySelector('.request-path').value;
                const headers = { Accept: 'application/json' }; const options = { method: definition.method, headers };
                if (definition.auth) headers.Authorization = `Bearer ${elements.token.value}`;
                if (definition.deviceKey) headers['X-Device-Key'] = card.querySelector('.device-key').value;
                if (definition.body) {
                    try {
                        const body = JSON.parse(card.querySelector('.request-body').value);
                        if (definition.form) { headers['Content-Type'] = 'application/x-www-form-urlencoded'; options.body = new URLSearchParams(body); } else { headers['Content-Type'] = 'application/json'; options.body = JSON.stringify(body); }
                    } catch (error) { writeResponse(responseOutput, { error: 'Request JSON không hợp lệ.', detail: error.message }); return; }
                }
                button.disabled = true; button.textContent = 'Đang gửi...'; writeResponse(responseOutput, 'Đang chờ API trả lời...');
                try {
                    const response = await fetch(apiUrl(path), options); const contentType = response.headers.get('content-type') || ''; const body = contentType.includes('application/json') ? await response.json() : await response.text();
                    writeResponse(responseOutput, body);
                    if (definition.path === '/auth/login' && response.ok && body.data?.token) setToken(body.data.token);
                    if (definition.path === '/auth/logout' && response.ok) setToken('');
                } catch (error) { writeResponse(responseOutput, { error: 'Không thể gọi API.', detail: error.message }); }
                finally { button.disabled = false; button.textContent = 'Gửi request'; }
            }

            async function copyText(value, button) { try { await navigator.clipboard.writeText(value); const text = button.textContent; button.textContent = 'Đã copy'; setTimeout(() => { button.textContent = text; }, 1200); } catch { button.textContent = 'Không thể copy'; } }
            function logWebSocket(message, data) { const suffix = data === undefined ? '' : `\n${json(data)}`; elements.wsLog.textContent = `[${new Date().toLocaleTimeString('vi-VN')}] ${message}${suffix}\n\n${elements.wsLog.textContent}`; }

            async function authenticateChannel() {
                const channelName = `private-devices.${document.getElementById('ws-device-id').value}`;
                const response = await fetch(apiUrl('/broadcasting/auth'), { method: 'POST', headers: { Accept: 'application/json', Authorization: `Bearer ${elements.token.value}`, 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ socket_id: socketId, channel_name: channelName }) });
                const authorization = await response.json(); if (!response.ok) throw new Error(authorization.message || 'Xác thực channel thất bại.');
                socket.send(JSON.stringify({ event: 'pusher:subscribe', data: { channel: channelName, ...authorization } })); logWebSocket(`Đã subscribe ${channelName}.`, authorization);
            }

            function connectWebSocket() {
                if (!elements.token.value) { elements.wsStatus.value = 'Cần Bearer token'; return; }
                socket?.close(); const url = `${document.getElementById('reverb-url').value.replace(/\/$/, '')}/app/${document.getElementById('reverb-key').value}?protocol=7&client=api-playground&version=1.0&flash=false`;
                elements.wsStatus.value = 'Đang kết nối'; logWebSocket(`Mở ${url}`); socket = new WebSocket(url);
                socket.onmessage = async ({ data }) => { const frame = JSON.parse(data); const payload = typeof frame.data === 'string' ? JSON.parse(frame.data) : frame.data; logWebSocket(`Nhận ${frame.event}.`, payload); if (frame.event === 'pusher:connection_established') { socketId = payload.socket_id; elements.wsStatus.value = `Đã kết nối: ${socketId}`; try { await authenticateChannel(); } catch (error) { elements.wsStatus.value = error.message; logWebSocket(error.message); } } if (frame.event === 'pusher:ping') socket.send(JSON.stringify({ event: 'pusher:pong', data: {} })); };
                socket.onerror = () => { elements.wsStatus.value = 'Lỗi WebSocket'; logWebSocket('WebSocket báo lỗi.'); }; socket.onclose = () => { if (elements.wsStatus.value !== 'Lỗi WebSocket') elements.wsStatus.value = 'Đã ngắt kết nối'; };
            }

            const savedToken = localStorage.getItem(storageKey); if (savedToken) setToken(savedToken); else setToken('');
            definitions.forEach((definition) => renderEndpoint(definition, document.getElementById(`group-${definition.category}`)));
            document.getElementById('save-token').addEventListener('click', () => setToken(elements.token.value.trim())); document.getElementById('clear-token').addEventListener('click', () => setToken(''));
            document.getElementById('connect-websocket').addEventListener('click', connectWebSocket); document.getElementById('disconnect-websocket').addEventListener('click', () => socket?.close()); document.querySelector('[data-copy-target="ws-log"]').addEventListener('click', (event) => copyText(elements.wsLog.textContent, event.currentTarget));
        </script>
    </body>
</html>

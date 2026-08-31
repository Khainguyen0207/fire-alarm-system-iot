<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Http\Resources\DeviceResource;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class DeviceController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->respond(DeviceResource::collection(Device::query()->with('sensors')->paginate(50))->resolve(), 'Devices retrieved successfully.');
    }

    public function store(StoreDeviceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $device = Device::query()->create([
            'id' => $data['id'], 'name' => $data['name'], 'status' => $data['status'],
            'api_key_hash' => Hash::make($data['apiKey']), 'installed_at' => $data['installedAt'] ?? null,
        ]);

        return $this->respond((new DeviceResource($device))->resolve(), 'Device created successfully.', 201);
    }

    public function show(Device $device): JsonResponse
    {
        return $this->respond((new DeviceResource($device->load('sensors')))->resolve(), 'Device retrieved successfully.');
    }

    public function update(UpdateDeviceRequest $request, Device $device): JsonResponse
    {
        $data = $request->validated();
        if (isset($data['apiKey'])) {
            $data['api_key_hash'] = Hash::make($data['apiKey']);
            unset($data['apiKey']);
        }
        if (array_key_exists('installedAt', $data)) {
            $data['installed_at'] = $data['installedAt'];
            unset($data['installedAt']);
        }
        $device->update($data);

        return $this->respond((new DeviceResource($device->fresh('sensors')))->resolve(), 'Device updated successfully.');
    }

    public function destroy(Device $device): JsonResponse
    {
        $device->delete();

        return $this->respond(null, 'Device deleted successfully.');
    }

    private function respond(mixed $data, string $message, int $status = 200): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data, 'message' => $message, 'errors' => null], $status);
    }
}

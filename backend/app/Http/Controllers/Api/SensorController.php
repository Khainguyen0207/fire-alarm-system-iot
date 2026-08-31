<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSensorRequest;
use App\Http\Requests\UpdateSensorRequest;
use App\Http\Resources\SensorResource;
use App\Models\Device;
use App\Models\Sensor;
use Illuminate\Http\JsonResponse;

class SensorController extends Controller
{
    public function index(Device $device): JsonResponse
    {
        return $this->respond(SensorResource::collection($device->sensors()->paginate(50))->resolve(), 'Sensors retrieved successfully.');
    }

    public function store(StoreSensorRequest $request, Device $device): JsonResponse
    {
        $sensor = $device->sensors()->create($request->validated());

        return $this->respond((new SensorResource($sensor))->resolve(), 'Sensor created successfully.', 201);
    }

    public function update(UpdateSensorRequest $request, Sensor $sensor): JsonResponse
    {
        $sensor->update($request->validated());

        return $this->respond((new SensorResource($sensor))->resolve(), 'Sensor updated successfully.');
    }

    public function destroy(Sensor $sensor): JsonResponse
    {
        $sensor->delete();

        return $this->respond(null, 'Sensor deleted successfully.');
    }

    private function respond(mixed $data, string $message, int $status = 200): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data, 'message' => $message, 'errors' => null], $status);
    }
}

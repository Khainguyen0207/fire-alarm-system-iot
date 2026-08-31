<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIoTDataRequest;
use App\Http\Resources\TelemetryResource;
use App\Models\Device;
use App\Services\TelemetryIngestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class IoTDataController extends Controller
{
    public function store(StoreIoTDataRequest $request, TelemetryIngestionService $telemetryIngestionService): JsonResponse
    {
        $device = Device::query()->findOrFail($request->validated('deviceId'));

        if ($device->status !== 'active' || ! Hash::check((string) $request->header('X-Device-Key'), $device->api_key_hash)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'The device credentials are invalid.',
                'errors' => null,
            ], 401);
        }

        $telemetry = $telemetryIngestionService->ingest($device, $request->validated(), $request->all());

        return response()->json([
            'success' => true,
            'data' => (new TelemetryResource($telemetry))->resolve($request),
            'message' => 'Telemetry received.',
            'errors' => null,
        ], 201);
    }
}

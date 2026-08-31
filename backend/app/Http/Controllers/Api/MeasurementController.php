<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListMeasurementsRequest;
use App\Http\Resources\SensorMeasurementMinuteResource;
use App\Http\Resources\SensorMeasurementResource;
use App\Http\Resources\TelemetryResource;
use App\Models\Device;
use App\Models\SensorMeasurement;
use App\Models\SensorMeasurementMinute;
use App\Services\FireStateService;
use App\Services\MeasurementQueryService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class MeasurementController extends Controller
{
    public function latest(Device $device, MeasurementQueryService $measurementQueryService, FireStateService $fireStateService): JsonResponse
    {
        $telemetry = $measurementQueryService->latest($device, $fireStateService);

        return response()->json([
            'success' => true,
            'data' => $telemetry === null ? null : (new TelemetryResource($telemetry))->resolve(),
            'message' => $telemetry === null ? 'No telemetry measurements are available.' : 'Latest telemetry retrieved successfully.',
            'errors' => null,
        ]);
    }

    public function index(ListMeasurementsRequest $request, Device $device): JsonResponse
    {
        $measurements = $this->applyFilters(
            SensorMeasurement::query()
                ->with('sensor:id,device_id,type')
                ->whereHas('sensor', fn (Builder $query) => $query->where('device_id', $device->id)),
            $request->validated(),
        )
            ->latest('recorded_at')
            ->paginate($request->integer('perPage', 50));

        return $this->paginatedResponse(
            SensorMeasurementResource::collection($measurements)->resolve(),
            $measurements,
            'Measurements retrieved successfully.',
        );
    }

    public function minutes(ListMeasurementsRequest $request, Device $device): JsonResponse
    {
        $measurements = $this->applyFilters(
            SensorMeasurementMinute::query()
                ->with('sensor:id,device_id,type')
                ->whereHas('sensor', fn (Builder $query) => $query->where('device_id', $device->id)),
            $request->validated(),
        )
            ->latest('recorded_at')
            ->paginate($request->integer('perPage', 50));

        return $this->paginatedResponse(
            SensorMeasurementMinuteResource::collection($measurements)->resolve(),
            $measurements,
            'Minute measurements retrieved successfully.',
        );
    }

    /**
     * @param  array{from?: string, to?: string, sensorType?: string}  $filters
     *
     * @template TModel of SensorMeasurement|SensorMeasurementMinute
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['from'])) {
            $query->where('recorded_at', '>=', CarbonImmutable::parse($filters['from'])->utc());
        }

        if (isset($filters['to'])) {
            $query->where('recorded_at', '<=', CarbonImmutable::parse($filters['to'])->utc());
        }

        if (isset($filters['sensorType'])) {
            $query->whereHas('sensor', fn (Builder $sensorQuery) => $sensorQuery->where('type', $filters['sensorType']));
        }

        return $query;
    }

    /**
     * @param  array<int, mixed>  $data
     */
    private function paginatedResponse(array $data, mixed $paginator, string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'errors' => null,
            'meta' => [
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}

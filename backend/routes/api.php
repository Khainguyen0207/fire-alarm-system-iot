<?php

use App\Http\Controllers\Api\IoTDataController;
use App\Http\Controllers\Api\MeasurementController;
use Illuminate\Support\Facades\Route;

Route::post('iot/data', [IoTDataController::class, 'store']);

Route::prefix('devices/{device}')->group(function (): void {
    Route::get('measurements/latest', [MeasurementController::class, 'latest']);
    Route::get('measurements', [MeasurementController::class, 'index']);
    Route::get('measurements/minutes', [MeasurementController::class, 'minutes']);
});

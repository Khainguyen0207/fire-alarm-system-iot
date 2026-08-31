<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\IoTDataController;
use App\Http\Controllers\Api\MeasurementController;
use App\Http\Controllers\Api\SensorController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Support\Facades\Route;

Route::post('iot/data', [IoTDataController::class, 'store']);

Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::prefix('devices/{device}')->group(function (): void {
        Route::get('measurements/latest', [MeasurementController::class, 'latest']);
        Route::get('measurements', [MeasurementController::class, 'index']);
        Route::get('measurements/minutes', [MeasurementController::class, 'minutes']);
        Route::get('sensors', [SensorController::class, 'index']);
        Route::post('sensors', [SensorController::class, 'store']);
    });
    Route::apiResource('devices', DeviceController::class);
    Route::patch('sensors/{sensor}', [SensorController::class, 'update']);
    Route::delete('sensors/{sensor}', [SensorController::class, 'destroy']);
    Route::get('settings', [SettingController::class, 'index']);
    Route::patch('settings/{setting}', [SettingController::class, 'update']);
});

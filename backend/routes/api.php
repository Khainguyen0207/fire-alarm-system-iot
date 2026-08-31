<?php

use App\Http\Controllers\Api\IoTDataController;
use Illuminate\Support\Facades\Route;

Route::post('iot/data', [IoTDataController::class, 'store']);

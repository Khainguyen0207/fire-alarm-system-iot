<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->respond(SettingResource::collection(Setting::query()->orderBy('key')->get())->resolve(), 'Settings retrieved successfully.');
    }

    public function update(UpdateSettingRequest $request, Setting $setting): JsonResponse
    {
        $setting->update(['value' => $request->validated('value')]);

        return $this->respond((new SettingResource($setting))->resolve(), 'Setting updated successfully.');
    }

    private function respond(mixed $data, string $message): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data, 'message' => $message, 'errors' => null]);
    }
}

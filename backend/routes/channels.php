<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('devices.{deviceId}', function (User $user, string $deviceId): bool {
    return true;
});

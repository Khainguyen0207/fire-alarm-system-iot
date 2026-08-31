<?php

namespace App\Events;

use App\Http\Resources\TelemetryResource;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TelemetryReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @param array<string, mixed> $telemetry */
    public function __construct(public array $telemetry) {}

    public function broadcastAs(): string
    {
        return 'telemetry.received';
    }

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('devices.'.$this->telemetry['device']->id)];
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return (new TelemetryResource($this->telemetry))->resolve();
    }
}

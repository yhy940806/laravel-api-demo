<?php

namespace App\Events\Common;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateService
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $arrAuthGroup;

    /**
     * Create a new event instance.
     *
     * @param array $arrAuthGroup
     */
    public function __construct(array $arrAuthGroup)
    {
        $this->arrAuthGroup = $arrAuthGroup;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}

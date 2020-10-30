<?php

namespace App\Events\Common;

use App\Models\Soundblock\Service;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class UpdateService {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Service $objService;

    public Service $objNewService;

    /**
     * Create a new event instance.
     *
     * @param Service $objService
     * @param Service $objNewService
     */
    public function __construct(Service $objService, Service $objNewService) {
        $this->objService = $objService;
        $this->objNewService = $objNewService;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {
        return new PrivateChannel('channel-name');
    }
}

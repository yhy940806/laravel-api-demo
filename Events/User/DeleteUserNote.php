<?php

namespace App\Events\User;

use App\Models\UserNote;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class DeleteUserNote {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public UserNote $objNote;

    /**
     * Create a new event instance.
     *
     * @param UserNote $objNote
     */
    public function __construct(UserNote $objNote) {
        $this->objNote = $objNote;
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

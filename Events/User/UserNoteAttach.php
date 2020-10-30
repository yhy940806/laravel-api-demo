<?php

namespace App\Events\User;

use App\Models\UserNote;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class UserNoteAttach {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public UserNote $objNote;

    public array $arrUrls;

    /**
     * Create a new event instance.
     *
     * @param UserNote $objNote
     * @param array $arrUrls
     */
    public function __construct(UserNote $objNote, array $arrUrls) {
        $this->objNote = $objNote;
        $this->arrUrls = $arrUrls;
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

<?php

namespace App\Events\Soundblock;

use Illuminate\Queue\SerializesModels;
use App\Models\Soundblock\ServiceNote;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ServiceNoteAttach {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ServiceNote $objNote;

    public array $urls;

    /**
     * Create a new event instance.
     *
     * @param ServiceNote $objNote
     * @param array $attachmentUrl
     */
    public function __construct(ServiceNote $objNote, array $attachmentUrl) {
        $this->objNote = $objNote;
        $this->urls = $attachmentUrl;
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

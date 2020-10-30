<?php

namespace App\Events\Common;

use App\Models\{Core\App, User};
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class PrivateNotification
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var User */
    public User $receiver;

    /** @var array $contents */
    public array $contents;

    /** @var array $flags */
    public array $flags;

    /** @var App */
    public App $app;

    /**
     * Create a new event instance.
     * @param User $receiver
     * @param array $contents
     * @param array $flags
     * @param App $app
     */
    public function __construct(User $receiver, array $contents, array $flags, App $app) {
        $this->receiver = $receiver;
        $this->contents = $contents;
        $this->flags = $flags;
        $this->app = $app;
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

<?php

namespace App\Events\Common;

use Util;
use Exception;
use App\Models\{User};
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\{InteractsWithSockets, PrivateChannel};

class UserNotification implements ShouldBroadcast {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var array
     */
    public array $contents;
    /**
     * @var User
     */
    public User $receiver;
    /**
     * @var array
     */
    public array $appNames;

    /**
     * Create a new event instance.
     * @param array $contents
     * @param User $receiver
     * @param array $appNames
     * @throws Exception
     */
    public function __construct(array $contents, User $receiver, array $appNames) {
        if (!Util::array_keys_exists(["notification_name"], $contents))
            throw new Exception("Invalid Parameter.", 400);

        $this->contents = $contents;
        $this->receiver = $receiver;
        $this->appNames = $appNames;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {
        // e.g channel.app.soundblock.user.{{user_uuid}}
        /** @var array */
        $channels = [];

        foreach ($this->appNames as $appName) {
            array_push($channels, new PrivateChannel(sprintf("channel.%s.user.%s", strtolower($appName), $this->receiver->user_uuid)));
        }

        return ($channels);
    }

    public function broadcastAs() {
        return ("Notify.User." . $this->receiver->user_uuid);
    }

    public function broadcastWith() {
        return ($this->contents);
    }
}

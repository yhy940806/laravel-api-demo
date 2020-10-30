<?php

namespace App\Events\Soundblock;

use Illuminate\Queue\SerializesModels;
use App\Models\{User, Soundblock\Project};
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ProjectGroup {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var User
     */
    public User $user;
    /**
     * @var Project
     */
    public Project $project;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param Project $project
     */
    public function __construct(User $user, Project $project) {
        //
        $this->user = $user;
        $this->project = $project;
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

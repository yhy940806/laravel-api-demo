<?php

namespace App\Events\Soundblock;

use App\Models\UserContactEmail;
use App\Models\Soundblock\Project;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class InviteGroup {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Collection
     */
    public Collection $arrEmail;
    /**
     * @var Project
     */
    public Project $objProject;

    /**
     * Create a new event instance.
     * @param Collection|array $emails
     *
     * @param Project $objProject
     * @throws \Exception
     */
    public function __construct($emails, Project $objProject) {
        if ($emails instanceof Collection) {
            $this->arrEmail = $emails;
        } else if ($emails instanceof UserContactEmail) {
            $this->arrEmail = collect()->push($emails);
        } else {
            throw new \Exception("emails is invalid parameter.", 417);
        }

        $this->objProject = $objProject;
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

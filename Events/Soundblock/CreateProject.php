<?php

namespace App\Events\Soundblock;

use App\Models\User;
use App\Models\Soundblock\Project;
use Illuminate\Queue\SerializesModels;

class CreateProject {
    use SerializesModels;

    /**
     * @var Project $objProject
     */
    public Project $objProject;
    /**
     * @var User $objUser
     */
    public User $objUser;

    /**
     * Create a new event instance.
     *
     * @param Project $objProject
     * @param User $objUser
     */
    public function __construct(Project $objProject, User $objUser) {
        $this->objProject = $objProject;
        $this->objUser = $objUser;
    }
}

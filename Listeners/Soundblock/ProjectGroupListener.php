<?php

namespace App\Listeners\Soundblock;

use App\Events\Soundblock\ProjectGroup;
use App\Models\Core\Auth\AuthGroup;
use App\Models\Soundblock\Project;
use App\Models\User;
use Client;
use Constant;
use App\Services\Core\Auth\{AuthGroupService, AuthPermissionService};

class ProjectGroupListener {
    /**
     * @var AuthGroupService $groupService
     */
    private AuthGroupService $groupService;
    /**
     * @var AuthPermissionService
     */
    private AuthPermissionService $permissionService;

    /**
     * Create the event listener.
     * @param AuthGroupService $groupService
     * @param AuthPermissionService $permissionService
     *
     * @return void
     */
    public function __construct(AuthGroupService $groupService, AuthPermissionService $permissionService) {
        $this->groupService = $groupService;
        $this->permissionService = $permissionService;
    }

    /**
     * Handle the event.
     *
     * @param ProjectGroup $event
     * @return void
     * @throws \Exception
     */
    public function handle($event) {
        /** @var User */
        $user = $event->user;
        /** @var Project */
        $project = $event->project;
        /** @var AuthGroup */
        $group = $this->groupService->findByProject($project);
        if ($this->groupService->checkIfUserExists($user, $group) != Constant::EXIST) {
            // Add a user to a group.
            $this->groupService->addUserToGroup($user, $group, Client::app());
            // Attach service level permissions to the user by default.
            $this->permissionService->attachUserPermissions(Constant::service_level_permissions(), $user, $group, 0);
        }
    }
}

<?php

namespace App\Listeners\Soundblock;

use Util;
use Client;
use Constant;
use App\Models\User;
use App\Events\Soundblock\CreateProject;
use App\Services\{Core\Auth\AuthGroupService, Core\Auth\AuthPermissionService, Soundblock\TeamService};

class CreateProjectListener {
    /** @var AuthGroupService */
    protected AuthGroupService $authGroupService;
    /** @var AuthPermissionService */
    protected AuthPermissionService $authPermService;
    /** @var TeamService */
    protected TeamService $teamService;

    /**
     * Create the event listener.
     * @param AuthGroupService $authGroupService
     * @param AuthPermissionService $authPermService
     * @param TeamService $teamService
     * @return void
     */
    public function __construct(AuthGroupService $authGroupService, AuthPermissionService $authPermService, TeamService $teamService) {
        $this->authGroupService = $authGroupService;
        $this->authPermService = $authPermService;
        $this->teamService = $teamService;
    }

    /**
     * Handle the event.
     *
     * @param CreateProject $event
     * @return void
     */
    public function handle(CreateProject $event) {
        $objAuth = Client::auth();
        $objProject = $event->objProject;
        $objUser = $event->objUser;

        $arrGroup = [];

        $arrGroup["auth_id"] = $objAuth->auth_id;
        $arrGroup["auth_uuid"] = $objAuth->auth_uuid;
        $arrGroup["group_name"] = Util::makeGroupName($objAuth, "project", $objProject);
        $arrGroup["group_memo"] = Util::makeGroupMemo($objAuth, "project", $objProject);
        $arrGroup["flag_critical"] = true;
        // create the project group for this project.
        $objAuthGroup = $this->authGroupService->create($arrGroup, true);

        $this->authPermService->attachGroupPermissions(Constant::project_level_permissions(), $objAuthGroup);
        // Add the user to project group.
        $this->authGroupService->addUserToGroup($objUser, $objAuthGroup, Client::app());
        $this->authPermService->attachUserPermissions(Constant::user_level_permissions(), $objUser, $objAuthGroup);
        // Create a new Team
        $this->teamService->create($objProject);
    }
}

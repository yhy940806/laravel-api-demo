<?php

namespace App\Listeners\Soundblock;

use Util;
use Client;
use App\Models\Soundblock\Project;
use App\Events\Soundblock\InviteGroup;
use App\Services\{Core\Auth\AuthGroupService, Soundblock\TeamService};

class InviteGroupListener {
    protected AuthGroupService $authGroupService;

    protected TeamService $teamService;

    /**
     * Create the event listener.
     *
     * @param AuthGroupService $authGroupService
     * @param TeamService $teamService
     */
    public function __construct(AuthGroupService $authGroupService, TeamService $teamService) {
        $this->authGroupService = $authGroupService;
        $this->teamService = $teamService;
    }

    /**
     * Handle the event.
     *
     * @param InviteGroup $event
     * @return void
     */
    public function handle($event) {
        $arrEmail = $event->arrEmail;
        /** @var Project */
        $objProject = $event->objProject;
        $objAuth = Client::auth();
        $objApp = Client::app();
        $arrUsers = collect();
        foreach ($arrEmail as $email) {
            $arrUsers->push($email->user);
        }
        $groupName = Util::makeGroupName($objAuth, "project", $objProject);
        $objAuthGroup = $this->authGroupService->findByName($groupName);
        $objAuthGroup = $this->authGroupService->addUsersToGroup($arrUsers, $objAuthGroup, $objApp);
    }
}

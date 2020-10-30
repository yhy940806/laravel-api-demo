<?php

namespace App\Listeners\Soundblock;

use App\Models\Soundblock\Invites;
use App\Models\User;
use Mail;
use Util;
use Client;
use App\Mail\Soundblock\InviteMail;
use App\Services\Soundblock\TeamService;

class InviteTeamListener {
    protected TeamService $teamService;

    /**
     * Create the event listener.
     *
     * @param TeamService $teamService
     */
    public function __construct(TeamService $teamService) {
        $this->teamService = $teamService;
    }

    /**
     * Handle the event.
     *
     * @param \App\Events\Soundblock\InviteTeam $event
     * @return void
     * @throws \Exception
     */
    public function handle($event) {
        $arrInfo = $event->arrInfo;
        $team = $event->team;

        $this->teamService->addMembers($team, $arrInfo);
        foreach ($arrInfo as $info) {
            /** @var User */
            $user = $info["email"]->user;
            /** @var Invites */
            $invite = $team->invite()->create([
                "invite_uuid"  => Util::uuid(),
                "table_field"  => $team->getKeyName(),
                "invite_name"  => $info["email"]->user->name,
                "invite_email" => $info["email"]->user_auth_email,
                "invite_role"  => $info["user_role"],
            ]);
            // Send mail
            Mail::to($user->recpient())->send(new InviteMail($team->project, Client::app(), $invite));
        }

    }
}

<?php

namespace App\Contracts\Soundblock\Invite;

use App\Models\User;
use App\Models\Soundblock\Invites;

interface InviteContract {
    public function getInviteByHash(string $hash): ?Invites;
    public function getInviteByEmail(string $email): ?Invites;
    public function useInvite(Invites $invite, array $userData): ?User;
    public function inviteSignIn(Invites $invite, array $userData): User;
}

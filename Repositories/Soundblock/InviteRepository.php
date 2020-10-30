<?php

namespace App\Repositories\Soundblock;

use App\Models\Soundblock\Invites;
use App\Repositories\BaseRepository;

class InviteRepository extends BaseRepository {
    /**
     * InviteRepository constructor.
     * @param Invites $invites
     */
    public function __construct(Invites $invites) {
        $this->model = $invites;
    }

    public function getInviteByHash(string $hash) : ?Invites {
        return $this->model->where("invite_hash", $hash)->first();
    }

    public function getInviteByEmail(string $email) : ?Invites {
        return $this->model->where("invite_email", $email)->first();
    }
}

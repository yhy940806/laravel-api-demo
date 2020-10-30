<?php

namespace App\Repositories\User;

use App\Models\UserCorrespondence;
use App\Repositories\BaseRepository;

class UserCorrespondenceRepository extends BaseRepository {
    public function __construct(UserCorrespondence $correspondence) {
        $this->model = $correspondence;
    }

    /**
     * @param string $emailId
     * @return UserCorrespondence|null
     */
    public function findByEmail(string $emailId): ?UserCorrespondence {
        return ($this->model->where("email_id", $emailId)->first());
    }
}

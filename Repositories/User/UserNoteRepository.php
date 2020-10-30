<?php

namespace App\Repositories\User;

use App\Models\UserNote;
use App\Repositories\BaseRepository;

class UserNoteRepository extends BaseRepository {
    public function __construct(UserNote $objNote) {
        $this->model = $objNote;
    }
}

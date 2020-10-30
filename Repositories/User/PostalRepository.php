<?php

namespace App\Repositories\User;

use App\Models\UserContactPostal;
use App\Repositories\BaseRepository;

class PostalRepository extends BaseRepository {
    public function __construct(UserContactPostal $objPostal) {
        $this->model = $objPostal;
    }
}

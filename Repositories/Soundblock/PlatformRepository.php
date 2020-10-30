<?php

namespace App\Repositories\Soundblock;

use App\Models\Soundblock\Platform;
use App\Repositories\BaseRepository;

class PlatformRepository extends BaseRepository {
    public function __construct(Platform $objPlatform) {
        $this->model = $objPlatform;
    }

    public function findAll(?int $perPage = null) {
        if ($perPage) {
            return ($this->model->paginate($perPage));
        } else {
            return ($this->model->get());
        }
    }
}

<?php

namespace App\Repositories\Soundblock;

use App\Repositories\BaseRepository;
use App\Models\Soundblock\CollectionHistory;

class CollectionHistoryRepository extends BaseRepository {
    public function __construct(CollectionHistory $objHistory) {
        $this->model = $objHistory;
    }
}

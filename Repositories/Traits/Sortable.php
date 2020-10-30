<?php

namespace App\Repositories\Traits;

use App\Models\BaseModel;

trait Sortable {
    public function setSortBy(string $sortBy = BaseModel::STAMP_CREATED) {
        $this->sortBy = $sortBy;
    }

    public function setSortOrder($sortOrder = "asc") {
        $this->sortOrder = $sortOrder;
    }
}

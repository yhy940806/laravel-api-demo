<?php

namespace App\Repositories\Traits;

trait Relationable {
    public ?array $relations = [];

    public function setRelations(array $relations = null) {
        $this->relations = $relations;
    }
}

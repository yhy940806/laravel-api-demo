<?php

namespace App\Repositories\Soundblock;

use App\Repositories\BaseRepository;
use App\Models\Soundblock\ProjectNote;

class ProjectNoteRepository extends BaseRepository {
    public function __construct(ProjectNote $objNote) {
        $this->model = $objNote;
    }
}

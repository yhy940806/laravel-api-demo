<?php

namespace App\Repositories\Soundblock;

use App\Repositories\BaseRepository;
use App\Models\Soundblock\ProjectDraft;

class ProjectDraftRepository extends BaseRepository {

    /**
     * @param ProjectDraft $projectDraft
     * @return void
     */
    public function __construct(ProjectDraft $projectDraft) {
        $this->model = $projectDraft;
    }

}

<?php

namespace App\Repositories\Soundblock;

use App\Repositories\BaseRepository;
use App\Models\Soundblock\ProjectNoteAttachment;

class ProjectNoteAttachmentRepository extends BaseRepository {
    public function __construct(ProjectNoteAttachment $objAttach) {
        $this->model = $objAttach;
    }
}

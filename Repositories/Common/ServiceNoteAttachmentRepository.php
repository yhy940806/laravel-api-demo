<?php

namespace App\Repositories\Common;

use App\Repositories\BaseRepository;
use App\Models\Soundblock\ServiceNoteAttachment;

class ServiceNoteAttachmentRepository extends BaseRepository {
    public function __construct(ServiceNoteAttachment $objAttach) {
        $this->model = $objAttach;
    }
}

<?php

namespace App\Repositories\Common;

use App\Repositories\BaseRepository;
use App\Models\Soundblock\ServiceNote;

class ServiceNoteRepository extends BaseRepository {
    /**
     * @param ServiceNote $serviceNote
     * @return void
     */
    public function __construct(ServiceNote $serviceNote) {
        $this->model = $serviceNote;
    }
}

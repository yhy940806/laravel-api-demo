<?php

namespace App\Repositories\Office;

use App\Repositories\BaseRepository;
use App\Models\SupportTicketAttachment;

class SupportTicketAttachmentRepository extends BaseRepository {
    public function __construct(SupportTicketAttachment $objAttach) {
        $this->model = $objAttach;
    }
}

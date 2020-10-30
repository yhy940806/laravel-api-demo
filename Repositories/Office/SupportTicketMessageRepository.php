<?php

namespace App\Repositories\Office;

use App\Models\SupportTicketMessage;
use App\Repositories\BaseRepository;

class SupportTicketMessageRepository extends BaseRepository {

    public function __construct(SupportTicketMessage $objMessage) {
        $this->model = $objMessage;
    }
}

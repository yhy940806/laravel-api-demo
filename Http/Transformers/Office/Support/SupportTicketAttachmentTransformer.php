<?php

namespace App\Http\Transformers\Office\Support;

use App\Http\Transformers\BaseTransformer;
use App\Models\BaseModel;
use App\Models\SupportTicketAttachment;
use App\Traits\StampCache;

class SupportTicketAttachmentTransformer extends BaseTransformer
{

    use StampCache;
    public function transform(SupportTicketAttachment $objAttach)
    {
        $response = [
            "attachment_name" => $objAttach->attachment_name,
            "attachment_url" => $objAttach->attachment_url,
        ];

        return(array_merge($response, $this->stamp($objAttach)));
    }
}

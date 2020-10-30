<?php

namespace App\Http\Transformers\Soundblock;

use App\Http\Transformers\BaseTransformer;
use App\Models\Soundblock\ServiceNoteAttachment;
use App\Traits\StampCache;

class ServiceNoteAttachmentTransformer extends BaseTransformer
{
    use StampCache;

    public function transform(ServiceNoteAttachment $objAttach)
    {
        $response = [
            "attachment_url" => $objAttach->attachment_url,
        ];

        return(array_merge($response, $this->stamp($objAttach)));
    }
}

<?php

namespace App\Http\Transformers\Soundblock;

use App\Http\Transformers\BaseTransformer;
use App\Models\Soundblock\ProjectNoteAttachment;
use App\Traits\StampCache;

class ProjectNoteAttachmentTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(ProjectNoteAttachment $objAttach)
    {
        $response = [
            "attachment_url" => $objAttach->attachment_url,
        ];

        return(array_merge($response, $this->stamp($objAttach)));
    }
}

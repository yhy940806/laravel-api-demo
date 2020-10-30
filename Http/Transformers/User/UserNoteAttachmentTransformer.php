<?php

namespace App\Http\Transformers\User;

use App\Http\Transformers\BaseTransformer;
use App\Models\UserNoteAttachment;
use App\Traits\StampCache;

class UserNoteAttachmentTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(UserNoteAttachment $objAttach)
    {
        $response = [
            "attachment_url" => $objAttach->attachment_url,
        ];
        $response = array_merge($response, $this->stamp($objAttach));

        return($response);
    }
}

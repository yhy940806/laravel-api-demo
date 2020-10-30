<?php

namespace App\Http\Transformers\User;

use App\Http\Transformers\BaseTransformer;
use App\Models\UserNote;
use App\Traits\StampCache;
use Util;

class UserNoteTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(UserNote $objNote)
    {

        $objUser = $objNote->user;
        $response = [
            "note_uuid" => $objNote->note_uuid,
            "user_notes" => $objNote->user_notes,
            "user" => [
                "data" => [
                    "user_uuid" => $objUser->user_uuid,
                    "avatar_url" => Util::avatar_url($objUser),
                    "name_first" => $objUser->name_first,
                    "name_middle" => $objUser->name_middle,
                    "name_last" => $objUser->name_last,
                ]
            ],
        ];
        $response = array_merge($response, $this->stamp($objNote));

        return($response);
    }

    public function includeAttachments(UserNote $objNote)
    {
        return($this->collection($objNote->attachments, new UserNoteAttachmentTransformer));
    }
}

<?php

namespace App\Http\Transformers\Soundblock;

use App\Http\Transformers\BaseTransformer;
use App\Http\Transformers\User\UserTransformer;
use App\Models\BaseModel;
use App\Models\Soundblock\ServiceNote;
use Cache;
use App\Models\User;
use App\Traits\StampCache;
use Util;

class ServiceNoteTransformer extends BaseTransformer
{
    use StampCache;

    public function transform(ServiceNote $objNote)
    {
        $response = [
            "note_uuid" => $objNote->note_uuid,
            "service_uuid" => $objNote->service_uuid,
            "service_notes" => $objNote->service_notes,
            "user" => [
                "data" => [
                    "user_uuid" => $objNote->user->user_uuid,
                    "avatar_url" => Util::avatar_url($objNote->user),
                    "name_first" => $objNote->user->name_first,
                    "name_middle" => $objNote->user->name_middle,
                    "name_last" => $objNote->user->name_last,
                ]
            ],
        ];

        return(array_merge($response, $this->stamp($objNote)));
    }

    public function includeAttachments(ServiceNote $objNote)
    {
        return($this->collection($objNote->attachments, new ServiceNoteAttachmentTransformer));
    }

    public function includeUser(ServiceNote $objNote)
    {
        return($this->item($objNote->user, new UserTransformer(["avatar"])));
    }
}

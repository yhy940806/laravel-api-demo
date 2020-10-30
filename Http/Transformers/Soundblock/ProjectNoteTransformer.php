<?php

namespace App\Http\Transformers\Soundblock;

use App\Http\Transformers\BaseTransformer;
use App\Http\Transformers\User\UserTransformer;
use App\Models\Soundblock\ProjectNote;
use App\Traits\StampCache;

class ProjectNoteTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(ProjectNote $objNote)
    {
        $response = [
            "note_uuid" => $objNote->note_uuid,
            "project_uuid" => $objNote->project_uuid,
            "project_notes" => $objNote->project_notes,
        ];

        return(array_merge($response, $this->stamp($objNote)));
    }

    public function includeAttachments(ProjectNote $objNote)
    {
        return($this->collection($objNote->attachments, new ProjectNoteAttachmentTransformer));
    }

    public function includeUser(ProjectNote $objNote)
    {
        return($this->item($objNote->user, new UserTransformer(["avatar"])));
    }
}

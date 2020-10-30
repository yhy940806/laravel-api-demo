<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;

class ServiceNoteAttachment extends BaseModel
{
    protected $table = "soundblock_services_notes_attachments";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $hidden = [
        "row_id", "note_id",
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT, BaseModel::DELETED_AT,
        BaseModel::STAMP_CREATED_BY, BaseModel::STAMP_UPDATED_BY, BaseModel::STAMP_DELETED_BY
    ];

    protected $guarded = [];

    public function note()
    {
        return($this->belongsTo(ServiceNote::class, "note_id", "note_id"));
    }
}

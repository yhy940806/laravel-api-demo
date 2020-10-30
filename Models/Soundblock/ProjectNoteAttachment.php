<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;

class ProjectNoteAttachment extends BaseModel
{
    protected $table = "soundblock_projects_notes_attachments";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $hidden = [
        "row_id", "note_id", "row_uuid",
        BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT, BaseModel::MODIFIED_AT, BaseModel::STAMP_MODIFIED, BaseModel::STAMP_MODIFIED_BY
    ];

    protected $guarded = [];

    public function note()
    {
        return($this->belongsTo(ProjectNote::class, "note_id", "note_id"));
    }
}

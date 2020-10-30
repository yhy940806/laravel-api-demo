<?php

namespace App\Models;

class UserNoteAttachment extends BaseModel {
    protected $table = "users_notes_attachments";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $hidden = [
        "row_id", "note_id",
        BaseModel::CREATED_AT, BaseModel::STAMP_CREATED_BY, BaseModel::UPDATED_AT, BaseModel::STAMP_UPDATED_BY,
        BaseModel::DELETED_AT, BaseModel::STAMP_DELETED_BY, BaseModel::STAMP_DELETED,
    ];

    public function note() {
        return ($this->belongsTo(UserNote::class, "note_id", "note_id"));
    }
}

<?php

namespace App\Models;

class UserNote extends BaseModel {
    protected $table = "users_notes";

    protected $primaryKey = "note_id";

    protected string $uuid = "note_uuid";

    protected $hidden = [
        "note_id", "user_id",
        BaseModel::CREATED_AT, BaseModel::STAMP_CREATED_BY, BaseModel::UPDATED_AT, BaseModel::STAMP_UPDATED_BY,
        BaseModel::DELETED_AT, BaseModel::STAMP_DELETED_BY, BaseModel::STAMP_DELETED,
    ];

    protected $guarded = [];

    public function user() {
        return ($this->belongsTo(User::class, "user_id", "user_id"));
    }

    public function attachments() {
        return ($this->hasMany(UserNoteAttachment::class, "note_id", "note_id"));
    }
}

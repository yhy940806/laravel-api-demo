<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;
use App\Models\User;

class ProjectNote extends BaseModel
{
    protected $table = "soundblock_projects_notes";

    protected $primaryKey = "note_id";

    protected string $uuid = "note_uuid";

    protected $hidden = [
        "note_id", "project_id", "user_id", BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        "pivot", BaseModel::MODIFIED_AT, BaseModel::STAMP_MODIFIED_BY
    ];

    protected $guarded = [];

    public function attachments()
    {
        return($this->hasMany(ProjectNoteAttachment::class, "note_id", "note_id"));
    }

    public function user()
    {
        return($this->belongsTo(User::class, "user_id", "user_id"));
    }

    public function project()
    {
        return($this->belongsTo(Project::class, "project_id", "project_id"));
    }
}

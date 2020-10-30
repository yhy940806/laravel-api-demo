<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;
use App\Models\User;

class ServiceNote extends BaseModel
{
    protected $table = "soundblock_services_notes";

    protected $primaryKey = "note_id";

    protected string $uuid = "note_uuid";

    protected $hidden = [
        "note_id", "service_id", "service_uuid",
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT, BaseModel::DELETED_AT,
        BaseModel::STAMP_CREATED_BY, BaseModel::STAMP_UPDATED_BY, BaseModel::STAMP_DELETED_BY
    ];

    protected $guarded = [];

    public function attachments()
    {
        return($this->hasMany(ServiceNoteAttachment::class, "note_id", "note_id"));
    }

    public function service()
    {
        return($this->belongsTo(Service::class, "service_id", "service_id"));
    }

    public function user()
    {
        return($this->belongsTo(User::class, "user_id", "user_id"));
    }
}

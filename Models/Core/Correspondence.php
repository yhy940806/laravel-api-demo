<?php

namespace App\Models\Core;

use App\Models\Core\App;
use App\Models\BaseModel;
use App\Models\UserContactEmail;

class Correspondence extends BaseModel
{
    const UUID = "correspondence_uuid";

    protected $primaryKey = "correspondence_id";

    protected $table = "core_correspondence";

    protected $guarded = [];

    protected string $uuid = "correspondence_uuid";

    protected $hidden = [
        "correspondence_id", "app_id", "email_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED,
        BaseModel::STAMP_DELETED_BY, BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
    ];

    protected $casts = [
        "email_json"    => "array",
        "flag_read"     => "boolean",
        "flag_archived" => "boolean",
        "flag_received" => "boolean"
    ];

    public function app(){
        return $this->belongsTo(App::class, "app_id", "app_id");
    }

    public function contact_email(){
        return $this->hasOne(UserContactEmail::class, "row_id", "email_id");
    }

    public function attachments(){
        return $this->hasMany(CorrespondenceAttachment::class, "correspondence_id", "correspondence_id");
    }

    public function getEmailAttribute(){
        if (is_null($this->email_address)) {
            return ($this->contact_email->user_auth_email);
        } else {
            return ($this->email_address);
        }
    }
}

<?php

namespace App\Models;

use App\Models\Core\App;
use App\Models\Soundblock\Project;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends BaseModel {
    use SoftDeletes;

    protected $table = "notifications";

    protected $primaryKey = "notification_id";

    protected string $uuid = "notification_uuid";

    protected $guarded = [];

    protected $hidden = [
        "notification_id", "app_id", BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
    ];

    public function users() {
        return ($this->belongsToMany(User::class, "notifications_users", "notification_id", "user_id", "notification_id", "user_id")
                     ->whereNull("notifications_users." . BaseModel::STAMP_DELETED)
                     ->withPivot("notification_uuid", "user_uuid", "notification_state", "flag_canarchive", "flag_candelete", "flag_email")
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function app() {
        return ($this->belongsTo(App::class, "app_id", "app_id"));
    }
}

<?php

namespace App\Models;

use App\Models\Core\App;

class UserCorrespondence extends BaseModel {
    protected $table = "users_correspondence";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $guarded = [];

    protected $hidden = [
        "row_id", "row_uuid", "user_id", "app_id",
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT, BaseModel::DELETED_AT, BaseModel::STAMP_DELETED_BY, BaseModel::STAMP_DELETED,
    ];

    public function user() {
        return ($this->belongsTo(User::class, "user_id", "user_id"));
    }

    public function app() {
        return ($this->belongsTo(App::class, "app_id", "app_id"));
    }
}

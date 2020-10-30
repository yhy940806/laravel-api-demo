<?php

namespace App\Models\Core\Auth;


use App\Models\Core\App;
use App\Models\BaseModel;

class AuthModel extends BaseModel {
    protected $table = "core_auth";

    protected $primaryKey = "auth_id";

    protected string $uuid = "auth_uuid";

    protected $hidden = [
        "auth_id", BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
    ];

    public function app() {
        return ($this->belongsTo(App::class, "app_id", "app_id"));
    }
}

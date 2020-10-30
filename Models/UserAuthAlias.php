<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAuthAlias extends BaseModel {
    use SoftDeletes;

    protected $table = "users_auth_aliases";

    protected $primaryKey = "alias_id";

    protected string $uuid = "alias_uuid";

    protected $hidden = [
        "alias_id", "user_id",
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
    ];

    protected $guarded = [];

    public function user() {
        return ($this->belongsTo("App\Models\User", "user_id", "user_id"));
    }
}

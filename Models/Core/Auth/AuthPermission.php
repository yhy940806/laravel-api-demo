<?php

namespace App\Models\Core\Auth;

use App\Models\User;
use App\Models\Core\App;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuthPermission extends BaseModel {
    use SoftDeletes;

    protected $table = "core_auth_permissions";

    protected $primaryKey = "permission_id";

    protected string $uuid = "permission_uuid";

    protected $hidden = [
        "permission_id", "auth_id", BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::STAMP_CREATED_BY, BaseModel::STAMP_UPDATED_BY,
        BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::STAMP_CREATED, BaseModel::STAMP_UPDATED,
        "pivot",
    ];

    protected $guarded = [];

    public function app() {
        return ($this->belongsTo(App::class, "app_id", "app_id"));
    }

    public function groups() {
        return ($this->belongsToMany(AuthGroup::class, "core_auth_permissions_groups", "permission_id", "group_id", "permission_id", "group_id")
                     ->withPivot("permission_id", "permission_uuid", "permission_value")
                     ->whereNull("core_auth_permissions_groups." . BaseModel::STAMP_DELETED)
                     ->withTimeStamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function pgroups() {
        return ($this->belongsToMany(AuthGroup::class, "core_auth_permissions_groups_users", "permission_id", "group_id", "permission_id", "group_id")
                     ->withPivot("user_id", "user_uuid", "permission_value")
                     ->whereNull("core_auth_permissions_groups_users." . BaseModel::STAMP_DELETED)
                     ->withTimeStamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function pusers() {
        return ($this->belongsToMany(User::class, "core_auth_permissions_groups_users", "permission_id", "user_id", "permission_id", "user_id")
                     ->withPivot("group_id", "group_uuid", "permission_value")
                     ->whereNull("core_auth_permissions_groups_users." . BaseModel::STAMP_DELETED)
                     ->withTimeStamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }
}

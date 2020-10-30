<?php

namespace App\Models\Core\Auth;

use App\Models\User;
use App\Models\BaseModel;
use App\Models\Office\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property integer $group_id
 * @property integer $permission_id
 */
class AuthGroup extends BaseModel {
    //
    use SoftDeletes;

    protected $table = "core_auth_groups";

    protected $primaryKey = "group_id";

    protected string $uuid = "group_uuid";

    protected $hidden = [
        "group_id", BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        "pivot", "auth_id", BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
    ];

    protected $guarded = [];

    public function users() {
        return ($this->belongsToMany(User::class, "core_auth_groups_users", "group_id", "user_id", "group_id", "user_id")
                     ->withPivot("app_id", "app_uuid", "group_uuid", "user_uuid")
                     ->whereNull("core_auth_groups_users." . BaseModel::STAMP_DELETED)
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function usersWithTrashed() {
        return ($this->belongsToMany(User::class, "core_auth_groups_users", "group_id", "user_id", "group_id", "user_id")
                     ->withPivot("app_id", "app_uuid", "group_uuid", "user_uuid")
                     ->withTrashed()
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function permissions() {
        return ($this->belongsToMany(AuthPermission::class, "core_auth_permissions_groups", "group_id", "permission_id", "group_id", "permission_id")
                     ->withPivot("permission_value", "permission_uuid", "group_uuid")
                     ->whereNull("core_auth_permissions_groups." . BaseModel::STAMP_DELETED)
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function pusers() {
        return ($this->belongsToMany(User::class, "core_auth_permissions_groups_users", "group_id", "user_id", "group_id", "user_id")
                     ->withPivot("permission_id", "permission_uuid", "permission_value")
                     ->whereNull("core_auth_permissions_groups_users." . BaseModel::STAMP_DELETED)
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function access_contacts() {
        return ($this->belongsToMany(Contact::class, "office_contact_groups", "group_id", "contact_id", "group_id", "contact_id")
                     ->withPivot("group_uuid", "contact_uuid")
                     ->whereNull("office_contact_groups." . BaseModel::STAMP_DELETED)
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function scopeSuperuser(Builder $builder) {
        return $builder->where("group_name", "Superuser");
    }
}

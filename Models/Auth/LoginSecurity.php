<?php

namespace App\Models\Auth;
use App\Models\{BaseModel, User};

class LoginSecurity extends BaseModel
{
    //
    protected $table = "users_login_security";

    protected $guarded = [];

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $hidden = [
        "row_id", "user_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
    ];

    public function user()
    {
        return($this->belongsTo(User::class, "user_id", "user_id"));
    }
}

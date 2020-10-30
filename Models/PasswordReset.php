<?php

namespace App\Models;

use App\Models\Casts\StampCast;

class PasswordReset extends BaseModel {
    const EXPIRED_AT = "stamp_expired_at";
    const STAMP_EXPIRED = "stamp_expired";
    const IDX_STAMP_EXPIRED = "idx_stamp-expired";
    const STAMP_EXPIRED_BY = "stamp_expired_by";

    protected $table = "users_password_reset";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $guard = [];

    protected $hidden = [
        "row_id", "user_id", BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        PasswordReset::EXPIRED_AT,
    ];

    protected $casts = [
        BaseModel::STAMP_CREATED_BY    => StampCast::class,
        BaseModel::STAMP_UPDATED_BY    => StampCast::class,
        PasswordReset::STAMP_EXITED_BY => StampCast::class,
    ];

    public function user() {
        return $this->belongsTo(User::class, "user_id", "user_id");
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method Builder notVerified()
 */
class UserContactEmail extends BaseModel {
    const EMAIL_AT = "stamp_email_at";
    const STAMP_EMAIL = "stamp_email";
    const IDX_STAMP_EMAIL = "idx_stamp-email";
    const STAMP_EMAIL_BY = "stamp_email_by";

    protected $table = "users_contact_emails";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $guarded = [];

    protected $hidden = [
        "row_id", "row_uuid", "user_id", "user_uuid", "verification_hash", BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::CREATED_AT, BaseModel::STAMP_CREATED_BY, BaseModel::UPDATED_AT, BaseModel::STAMP_UPDATED_BY,
        "stamp_email", "stamp_email_by", "stamp_email_at", BaseModel::STAMP_CREATED, BaseModel::STAMP_UPDATED,
    ];

    protected $observables = ["verified"];

    public function user() {
        return ($this->belongsTo(User::class, "user_id", "user_id"));
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeNotVerified(Builder $builder) {
        return $builder->where("flag_verified", false);
    }

    public function verified() {
        $this->fireModelEvent("verified", false);
    }
}

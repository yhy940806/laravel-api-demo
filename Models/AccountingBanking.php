<?php

namespace App\Models;

use App\Traits\EncryptsAttributes;

class AccountingBanking extends BaseModel
{
    use EncryptsAttributes;

    protected $table = "users_accounting_banking";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $hidden = [
        "row_id", "row_uuid", "user_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT, "user"
    ];

    protected array $encrypts = ["bank_name", "account_type", "account_number", "routing_number"];

    protected $guarded = [];

    protected $appends = [
        "bank_uuid"
    ];

    public function user()
    {
        return($this->belongsTo(User::class, "user_id", "user_id"));
    }

    public function getBankUuidAttribute() {
        return($this->row_uuid);
    }
}

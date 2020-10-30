<?php

namespace App\Models\Accounting;

use App\Models\BaseModel;

class AccountingType extends BaseModel
{
    protected $guarded = [];

    protected $primaryKey = "accounting_type_id";

    protected $hidden = [
        "accounting_type_id", "accounting_type_uuid",
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
    ];

    public function accountingTypeRates() {
        return $this->hasMany(AccountingTypeRate::class, "accounting_type_id", "accounting_type_id");
    }
}

<?php

namespace App\Models\Accounting;

use App\Models\BaseModel;
use App\Models\Soundblock\ServiceTransaction;

class AccountingTransaction extends BaseModel
{
    protected $guarded = [];

    protected $primaryKey = "transaction_id";

    protected $table = "accounting_transactions";

    protected $hidden = [
        "transaction_id", "app_id", "app_field_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY
    ];

    public function transactional()
    {
        return $this->hasOne(ServiceTransaction::class, "transaction_id");
    }
}

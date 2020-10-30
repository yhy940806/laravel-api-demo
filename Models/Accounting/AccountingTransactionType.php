<?php

namespace App\Models\Accounting;

use App\Models\BaseModel;
use App\Models\Core\App;
use App\Models\Accounting\AccountingInvoiceTransaction;

class AccountingTransactionType extends BaseModel
{
    //
    protected $table = "accounting_types_transactions";

    protected $primaryKey = "type_id";

    protected string $uuid = "type_uuid";

    protected $guarded = [];

    protected $hidden = [
        "type_id", "app_id", BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY
    ];

    public function app()
    {
        return ($this->belongsTo(App::class, "app_id", "app_id"));
    }

    public function invoiceTransactions()
    {
        return ($this->hasMany(AccountingInvoiceTransaction::class, "transaction_type", "type_id"));
    }
}

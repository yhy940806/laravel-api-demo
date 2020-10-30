<?php

namespace App\Models\Accounting;

use App\Models\Accounting\AccountingInvoice;
use App\Models\BaseModel;
use App\Models\Casts\StampCast;

class AccountingInvoiceTransaction extends BaseModel
{
    //
    protected $table = "accounting_invoices_transactions";

    protected $primaryKey = "row_id";

    protected $guarded = [];

    protected $hidden = [
        "row_id", "invoice_id", "transaction_id", "app_table", "app_field", "app_field_id",
        AccountingInvoiceTransaction::DISCOUNT_AT,
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT, BaseModel::DELETED_AT,
        BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY
    ];

    protected $casts = [
        AccountingInvoiceTransaction::STAMP_DISCOUNT_BY => StampCast::class,
        BaseModel::STAMP_CREATED_BY => StampCast::class,
        BaseModel::STAMP_UPDATED_BY => StampCast::class,
    ];

    protected string $uuid = "row_uuid";

    public function invoice()
    {
        return ($this->belongsTo(AccountingInvoice::class, "invoice_id", "invoice_id"));
    }

    public function transactional()
    {
        return ($this->morphTo());
    }

    public function transactionType()
    {
        return ($this->belongsTo(AccountingTransactionType::class, "transaction_type", "type_id"));
    }
}

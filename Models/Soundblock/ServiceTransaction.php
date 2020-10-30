<?php

namespace App\Models\Soundblock;

use App\Models\{Accounting\AccountingTransaction,
    BaseModel,
    Accounting\AccountingInvoice,
    Accounting\AccountingInvoiceTransaction,
    User};
use App\Models\Accounting\AccountingType;

class ServiceTransaction extends BaseModel
{
    protected $table = "soundblock_services_transactions";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $guarded = [];

    protected $hidden = [
        "row_id", "transaction_id", "service_id", "ledger_id", "user_id", "accounting_type_id",
        BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
    ];

    public function user()
    {
        return ($this->belongsTo(User::class, "user_id", "user_id"));
    }

    public function accountingTransaction()
    {
        return $this->belongsTo(AccountingTransaction::class, "transaction_id", "transaction_id");
    }

    public function accountingInvoiceTransactions()
    {
        return ($this->morphMany(AccountingInvoiceTransaction::class, "transactional", "app_table", "app_field_id"));
    }

    public function accountingInvoice()
    {
        return $this->belongsToMany(AccountingInvoice::class, "accounting_invoices_transactions", "transaction_id", "invoice_id", "transaction_id", "invoice_id");
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class, "ledger_id", "ledger_id");
    }

    public function accountingType()
    {
        return $this->belongsTo(AccountingType::class, "accounting_type_id", "accounting_type_id");
    }
}

<?php

namespace App\Models\Accounting;

use App\Models\{BaseModel, User};

class AccountingInvoiceNote extends BaseModel
{
    //
    protected $table = "accounting_invoices_notes";

    protected $primaryKey = "row_id";

    protected $guard = [];

    protected $hidden = ["row_id", "invoice_id", "user_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,];

    protected string $uuid = "row_uuid";

    public function user()
    {
        return ($this->belongsTo(User::class, "user_id", "user_id"));
    }

    public function invoice()
    {
        return ($this->belongsTo(AccountingInvoice::class, "invoice_id", "invoice_id"));
    }
}

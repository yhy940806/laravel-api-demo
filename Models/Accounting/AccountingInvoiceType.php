<?php

namespace App\Models\Accounting;

use App\Models\{BaseModel, Core\App};

class AccountingInvoiceType extends BaseModel
{
    //
    protected $table = "accounting_types_invoices";

    protected $primaryKey = "type_id";

    protected string $uuid = "type_uuid";

    protected $guarded = [];

    protected $hidden = [
        "type_id", "app_id", "app_uuid",
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT, BaseModel::DELETED_AT,
        BaseModel::STAMP_CREATED, BaseModel::STAMP_CREATED_BY, BaseModel::STAMP_UPDATED, BaseModel::STAMP_UPDATED_BY,
        BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY
    ];

    public function invoices()
    {
        return ($this->hasMany(AccountingInvoice::class, "invoice_type_id", "invoice_type_id"));
    }

    public function app()
    {
        return ($this->belongsTo(App::class, "app_id", "app_id"));
    }
}

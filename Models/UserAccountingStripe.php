<?php

namespace App\Models;

use App\Helpers\Util;
use App\Models\Accounting\AccountingInvoice;
use Illuminate\Support\Facades\Auth;

class UserAccountingStripe extends BaseModel {
    protected $table = "users_accounting_stripe";

    protected $primaryKey = "row_id";

    protected $guarded = [];

    protected $hidden = [
        "row_id", "user_id", "stripe_id", "pivot",
        BaseModel::CREATED_AT, BaseModel::STAMP_CREATED_BY, BaseModel::UPDATED_AT, BaseModel::STAMP_UPDATED_BY,
        BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
    ];

    protected $casts = [
        "invoices.pivot.payment_response" => "array",
    ];

    protected static function boot() {
        parent::boot();

        static::creating(function ($model) {
            $model->stamp_created = time();
            $model->stamp_updated = time();
            $model->row_uuid = Util::uuid();

            if (Auth::user()) {
                $model->user_uuid = Auth::user()->user_uuid;
                $model->stamp_created_by = Auth::id();
                $model->stamp_updated_by = Auth::id();
            } else {
                $model->stamp_created_by = 1;
                $model->stamp_updated_by = 1;
                $model->user_uuid = '';
            }
        });
    }

    public function invoices() {
        return ($this->belongsToMany(AccountingInvoice::class, "accounting_invoices_payments", "payment_id", "invoice_id")
                     ->whereNull("accounting_invoices_payments." . BaseModel::STAMP_DELETED)
                     ->withPivot("row_uuid", "invoice_uuid", "payment_uuid", "payment_response", "payment_status")
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }
}

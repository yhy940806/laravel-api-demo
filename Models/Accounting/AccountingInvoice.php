<?php

namespace App\Models\Accounting;

use App\Models\{BaseModel,
    Core\App,
    Accounting\AccountingInvoiceTransaction,
    Accounting\AccountingInvoiceType,
    UserAccountingStripe,
    Soundblock\ServiceTransaction,
    Accounting\Pivot\AccountingInvoicePaymentPivot};
use App\Models\Casts\StampCast;
use App\Services\AuthService;

class AccountingInvoice extends BaseModel
{
    protected $guarded = [];

    protected $primaryKey = "invoice_id";

    protected $table = "accounting_invoices";

    protected string $uuid = "invoice_uuid";

    protected $casts = [
        "invoice_line_items" => "array",
        BaseModel::STAMP_CREATED_BY => StampCast::class,
        BaseModel::STAMP_UPDATED_BY => StampCast::class,
        BaseModel::STAMP_DISCOUNT_BY => StampCast::class,
    ];

    protected $hidden = [
        "invoice_id", "app_id", "user_id", "invoice_type_id", "payment", "invoice_type",
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY, BaseModel::DISCOUNT_AT
    ];

    protected $appends = [
        "payment_detail",
    ];

    public function serviceTransactions()
    {
        return $this->belongsToMany(ServiceTransaction::class, "accounting_invoices_transactions", "invoice_id", "transaction_id", "invoice_id", "transaction_id");
    }

    public function transactions()
    {
        return($this->hasMany(AccountingInvoiceTransaction::class, "invoice_id", "invoice_id"));
    }

    public function payment()
    {
        return ($this->belongsToMany(UserAccountingStripe::class, "accounting_invoices_payments", "invoice_id", "payment_id")
            ->using(AccountingInvoicePaymentPivot::class)
            ->whereNull("accounting_invoices_payments." . BaseModel::STAMP_DELETED)
            ->withPivot("row_uuid", "invoice_uuid", "payment_uuid", "payment_response", "payment_status")
            ->withTimeStamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function invoiceType()
    {
        return ($this->belongsTo(AccountingInvoiceType::class, "invoice_type", "type_id"));
    }

    public function app()
    {
        return ($this->belongsTo(App::class, "app_id", "app_id"));
    }

    public function getPaymentDetailAttribute()
    {
        /** @var UserAccountingStripe */
        $objPayment = $this->payment->first();
        /** @var AuthService */
        $authService = resolve(AuthService::class);
        $arrReqOption = [
            "group"         => "App.Office.Admin",
            "permission"    => "App.Office.Admin.Default",
            "app"           => "office"
        ];
        if ($authService->checkAuth($arrReqOption) && $objPayment) {
            return([
                "payment_response"      => $objPayment->pivot->payment_response,
                "payment_status"        => $objPayment->pivot->payment_status
            ]);
        } else {
            return(null);
        }
    }

    public function getTypeAttribute()
    {
        /** @var AccountingInvoiceType */
        $objTypeInvoice = $this->invoiceType;
        return([
            "type_name"     => $objTypeInvoice->type_name,
            "type_code"     => $objTypeInvoice->type_code
        ]);
    }
}

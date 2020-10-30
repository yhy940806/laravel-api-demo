<?php

namespace App\Http\Transformers\Accounting;

use App\Traits\StampCache;
use App\Services\AuthService;
use App\Models\Accounting\AccountingInvoice;
use App\Http\Transformers\BaseTransformer;
use App\Http\Transformers\Common\AppTransformer;

class AccountingInvoiceTransformer extends BaseTransformer
{
    use StampCache;

    public function transform(AccountingInvoice $objInvoice)
    {
        $response = [
            "invoice_uuid"          => $objInvoice->invoice_uuid,
            "invoice_date"          => $objInvoice->invoice_date,
            "invoice_status"        => $objInvoice->invoice_status,
        ];
        /** @var AuthService */
        $authService = resolve(AuthService::class);
        $objPayment = $objInvoice->payment()->first();
        if ($objPayment && $authService->checkApp("office")) {
            $response = array_merge($response, [
                "payment" => [
                    "data" => [
                        "payment_response"      => $objPayment->pivot->payment_response,
                        "payment_status"        => $objPayment->pivot->payment_status,
                    ]
                ]
            ]);
        }

        return (array_merge($response, $this->stamp($objInvoice)));
    }

    public function includeApp(AccountingInvoice $objInvoice)
    {
        return ($this->item($objInvoice->app, new AppTransformer));
    }

    public function includeInvoiceType(AccountingInvoice $objInvoice)
    {
        return ($this->item($objInvoice->invoiceType, new FinanceInvoiceTypeTransformer));
    }
}

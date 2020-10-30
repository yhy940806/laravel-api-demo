<?php

namespace App\Http\Transformers\Account;

use App\Traits\StampCache;
use App\Models\Accounting\AccountingInvoice;
use App\Http\Transformers\BaseTransformer;
use App\Http\Transformers\Common\AppTransformer;

class InvoiceTransformer extends BaseTransformer
{
    use StampCache;

    public function transform(AccountingInvoice $objInvoice)
    {
        $response = [
            "payment"         => $objInvoice->payment,
            "transactions"    => $objInvoice->transactions,
            "invoice_type"    => $objInvoice->invoiceType,
            "payment_details" => $objInvoice->payment_detail
        ];

        return(array_merge($response, $this->stamp($objInvoice)));
    }

    public function includeApp(AccountingInvoice $objInvoice)
    {
        return ($this->item($objInvoice->app, new AppTransformer));
    }
}

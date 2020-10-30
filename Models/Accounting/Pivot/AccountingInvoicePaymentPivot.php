<?php

namespace App\Models\Accounting\Pivot;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountingInvoicePaymentPivot extends Pivot
{
    protected $casts = [
        "payment_response" => "array"
    ];
}

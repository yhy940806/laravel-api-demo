<?php

namespace App\Http\Transformers\Office\Accounting;

use App\Http\Transformers\BaseTransformer;
use App\Http\Transformers\User\UserTransformer;
use App\Models\Accounting\AccountingType;
use App\Models\Accounting\AccountingTypeRate;
use App\Models\Office\Contact;
use App\Traits\StampCache;

class AccountingTypeRateTransformer extends BaseTransformer {
    use StampCache;

    public function transform(AccountingTypeRate $accountingTypeRate) {
        return [
            "row_uuid"         => $accountingTypeRate->row_uuid,
            "accounting_type_uuid" => $accountingTypeRate->accounting_type_uuid,
            "accounting_version"   => $accountingTypeRate->accounting_version,
            "accounting_rate"      => $accountingTypeRate->accounting_rate,
        ];
    }
}

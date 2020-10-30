<?php

namespace App\Http\Transformers\Office\Accounting;

use App\Http\Transformers\BaseTransformer;
use App\Http\Transformers\User\UserTransformer;
use App\Models\Accounting\AccountingType;
use App\Models\Office\Contact;
use App\Traits\StampCache;

class AccountingTypeTransformer extends BaseTransformer
{
    use StampCache;

    public function transform(AccountingType $accountingType) {
        return [
            "accounting_type_uuid" => $accountingType->accounting_type_uuid,
            "accounting_type_name" => $accountingType->accounting_type_name,
            "accounting_type_memo" => $accountingType->accounting_type_memo
        ];
    }

    public function includeAccountingTypeRates(AccountingType $accountingType) {
        return $this->collection($accountingType->accountingTypeRates, new AccountingTypeRateTransformer());
    }

}

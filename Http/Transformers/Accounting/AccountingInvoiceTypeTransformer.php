<?php

namespace App\Http\Transformers\Accounting;

use App\Http\Transformers\BaseTransformer;
use App\Models\Accounting\AccountingInvoiceType;
use App\Services\AuthService;
use App\Traits\StampCache;

class AccountingInvoiceTypeTransformer extends BaseTransformer
{
    use StampCache;

    public function transform(AccountingInvoiceType $objInvoiceType)
    {
        $response = [
            "type_code"     => $objInvoiceType->type_code
        ];
        /** @var AuthService */
        $authService = resolve(AuthService::class);
        if ($authService->checkApp("office")) {
            $response = array_merge($response, [
                "type_uuid"     => $objInvoiceType->type_uuid,
                "type_name"     => $objInvoiceType->type_name,
            ]);
        }

        return (array_merge($response, $this->stamp($objInvoiceType)));
    }
}

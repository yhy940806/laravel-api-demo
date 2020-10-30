<?php

namespace App\Http\Transformers\User;

use App\Http\Transformers\BaseTransformer;
use App\Models\AccountingPaypal;
use App\Traits\StampCache;

class PaypalTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(AccountingPaypal $objPaypal)
    {
        $response = [
            "paypal_uuid" => $objPaypal->row_uuid,
            "paypal_email" => $objPaypal->paypal,
            "flag_primary" => $objPaypal->flag_primary,
        ];
        $response = array_merge($response, $this->stamp($objPaypal));

        return($response);
    }
}

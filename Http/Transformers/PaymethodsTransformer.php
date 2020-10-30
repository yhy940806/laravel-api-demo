<?php

namespace App\Http\Transformers;

use App\Models\UsersPaymethods;
use App\Traits\StampCache;
use League\Fractal\TransformerAbstract;

class PaymethodsTransformer extends TransformerAbstract {

    use StampCache;

    public function transform(UsersPaymethods $objPaymethods)
    {
        $response = [
            "user_uuid" => $objPaymethods->user_uuid,
            "paypal_email" => $objPaymethods->paypal_email,
            "account_number" => $objPaymethods->account_number,
            "routing_number" => $objPaymethods->routing_number,
        ];

        return(array_merge($response, $this->stamp($objPaymethods)));
    }
}

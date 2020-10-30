<?php

namespace App\Http\Transformers\User;

use App\Http\Transformers\BaseTransformer;
use App\Models\BaseModel;
use App\Models\UserContactPhone;
use App\Traits\StampCache;

class PhoneTransformer extends BaseTransformer
{

    use StampCache;
    public function transform(?UserContactPhone $objPhone)
    {
        if (is_null($objPhone)) {
            return [];
        }

        $response = [
            "phone_type" => $objPhone->phone_type,
            "phone_number" => $objPhone->phone_number,
            "flag_primary" => $objPhone->flag_primary,
        ];
        $response = array_merge($response, $this->stamp($objPhone));

        return($response);
    }
}

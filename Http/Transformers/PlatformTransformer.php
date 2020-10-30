<?php

namespace App\Http\Transformers;

use App\Models\Soundblock\Platform;
use App\Traits\StampCache;

class PlatformTransformer extends BaseTransformer
{
    use StampCache;

    public function transform(Platform $objPlatform)
    {
        $response = [
            "platform_uuid" => $objPlatform->platform_uuid,
            "platform_name" => $objPlatform->name,
        ];

        return(array_merge($response, $this->stamp($objPlatform)));
    }
}

<?php

namespace App\Http\Transformers\Common;

use App\Http\Transformers\BaseTransformer;
use App\Models\Core\App;
use App\Traits\StampCache;

class AppTransformer extends BaseTransformer
{
    use StampCache;
    public function transform(App $objApp)
    {
        $response = [
            "app_uuid" => $objApp->app_uuid,
            "app_name" => $objApp->app_name,
            "app_platform" => $objApp->app_platform,
        ];
        return(array_merge($response, $this->stamp($objApp)));
    }
}

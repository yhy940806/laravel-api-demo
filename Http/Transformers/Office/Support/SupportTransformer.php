<?php

namespace App\Http\Transformers\Office\Support;

use App\Http\Transformers\BaseTransformer;
use App\Http\Transformers\Common\AppTransformer;
use App\Models\Support;
use App\Traits\StampCache;

class SupportTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(Support $objSupport)
    {
        $response = [
            "support_uuid" => $objSupport->support_uuid,
            "support_category" => $objSupport->support_category,
            "app" => [
                "data" => [
                    "app_uuid" => $objSupport->app_uuid,
                    "app_name" => $objSupport->app->app_name,
                ]
            ],
        ];

        return(array_merge($response, $this->stamp($objSupport)));
    }

    public function includeApp(Support $objSupport)
    {
        return($this->item($objSupport->app, new AppTransformer));
    }
}

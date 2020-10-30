<?php

namespace App\Http\Transformers\Soundblock;

use App\Http\Transformers\BaseTransformer;
use App\Models\Soundblock\DeploymentStatus;
use App\Traits\StampCache;

class DeploymentStatusTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(DeploymentStatus $objStatus)
    {
        $response = [
            "deployment_status" => $objStatus->deployment_status,
            "deployment_memo" => $objStatus->deployment_memo,
        ];

        return (array_merge($response, $this->stamp($objStatus)));
    }
}

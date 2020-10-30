<?php

namespace App\Http\Transformers\Soundblock;

use App\Http\Transformers\BaseTransformer;
use App\Http\Transformers\PlatformTransformer;
use App\Models\Soundblock\Deployment;
use App\Traits\StampCache;

class DeploymentTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(Deployment $objDeployment)
    {
        $response = [
            "deployment_uuid" => $objDeployment->deployment_uuid,
            "distribution" => "All Territory",
        ];

        return(array_merge($response, $this->stamp($objDeployment)));
    }

    public function includePlatform(Deployment $objDeployment)
    {
        return($this->item($objDeployment->platform, new PlatformTransformer()));
    }

    public function includeStatus(Deployment $objDeployment)
    {
        return($this->item($objDeployment->has("status")->first()->status, new DeploymentStatusTransformer));
    }

    public function includeProject(Deployment $objDeployment)
    {
        return($this->item($objDeployment->project, new ProjectTransformer));
    }
}

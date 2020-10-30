<?php

namespace App\Http\Transformers\Soundblock;

use App\Http\Transformers\BaseTransformer;
use App\Http\Transformers\ServiceTransformer;
use App\Models\{
    BaseModel,
    User,
    Soundblock\ServicePlan
};
use App\Traits\StampCache;
use Cache;

class ServicePlanTransformer extends BaseTransformer
{
    use StampCache;

    public function transform(?ServicePlan $objPlan)
    {
        if (is_null($objPlan)) {
            return null;
        }

        $response = [
            "plan_uuid"         => $objPlan->plan_uuid,
            "ledger_uuid"       => $objPlan->ledger_uuid,
            "plan_type"         => $objPlan->plan_type,
            "plan_cost"         => $objPlan->plan_cost,
            "plan_day"          => $objPlan->plan_day,
            "flag_active"       => $objPlan->flag_active
        ];
        /** @var User */
        $objUser = $objPlan->service->user;
        if ($objUser->stripe) {
            $response = array_merge($response, ["payment" => encrypt($objUser->stripe->row_uuid)]);
        }
        return (array_merge($response, $this->stamp($objPlan)));
    }

    public function includeService(ServicePlan $objPlan)
    {
        if ($objPlan->service)
            return ($this->item($objPlan->service, new ServiceTransformer));
    }
}

<?php

namespace App\Repositories\Common;

use App\Repositories\BaseRepository;
use App\Models\Soundblock\ServicePlan;

class ServicePlanRepository extends BaseRepository {
    /**
     * @param ServicePlan $servicePlan
     * @return void
     */
    public function __construct(ServicePlan $servicePlan) {
        $this->model = $servicePlan;
    }
}

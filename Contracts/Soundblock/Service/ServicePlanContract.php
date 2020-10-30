<?php


namespace App\Contracts\Soundblock\Service;

use App\Models\User;
use App\Models\Soundblock\ServicePlan;

interface ServicePlanContract {
    public function find($id, bool $bnFailure = true) : ?ServicePlan;
    public function findByService($service) : ?ServicePlan;

    public function cancel(User $user);
    public function update(User $user, string $planName, float $planCost);
}

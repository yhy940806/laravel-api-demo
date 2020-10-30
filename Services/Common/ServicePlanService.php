<?php

namespace App\Services\Common;

use App\Models\{
    Accounting\AccountingType,
    Accounting\AccountingTypeRate,
    Soundblock\Service,
    Soundblock\ServicePlan,
    User,
};
use App\Helpers\Util;
use App\Contracts\Soundblock\Service\ServicePlanContract;
use App\Repositories\Common\{ServicePlanRepository, ServiceRepository};
use App\Repositories\Accounting\{AccountingTypeRepository, AccountingTypeRateRepository};

class ServicePlanService implements ServicePlanContract
{
    /** @var ServiceRepository */
    protected ServiceRepository $serviceRepo;
    /** @var ServicePlanRepository */
    protected ServicePlanRepository $planRepo;
    /** @var AccountingTypeRepository */
    protected AccountingTypeRepository $accountingTypeRepo;
    /** @var AccountingTypeRateRepository */
    protected AccountingTypeRateRepository $accountingTypeRateRepo;

    /**
     * @param ServiceRepository $serviceRepo
     * @param ServicePlanRepository $planRepo
     * @param AccountingTypeRepository $accountingTypeRepo
     * @param AccountingTypeRateRepository $accountingTypeRateRepo
     *
     * @return void
     */
    public function __construct(ServiceRepository $serviceRepo, ServicePlanRepository $planRepo, AccountingTypeRepository $accountingTypeRepo, AccountingTypeRateRepository $accountingTypeRateRepo)
    {
        $this->serviceRepo = $serviceRepo;
        $this->planRepo = $planRepo;
        $this->accountingTypeRepo = $accountingTypeRepo;
        $this->accountingTypeRateRepo = $accountingTypeRateRepo;
    }

    public function find($id, bool $bnFailure = true): ?ServicePlan
    {
        return ($this->planRepo->find($id, $bnFailure));
    }

    public function findByService($service): ?ServicePlan
    {
        $objService = $this->serviceRepo->find($service, true);

        return ($objService->plans);
    }

    public function cancel(User $user): Service
    {
        /** @var Service $objService */
        $objService = $user->service;

        if (is_null($objService)) {
            throw new \Exception("User's service not found.");
        }

        $objPlan =  $objService->plans()->active()->first();

        if (is_null($objPlan)) {
            throw new \Exception("User's service plan not found.");
        }

        $objService->flag_status = "canceled";
        $objPlan->flag_active = false;

        $objService->save();
        $objPlan->save();

        return $objService;
    }

    /**
     * @param Service $objService
     * @param string $strPlanType
     *
     * @return Service
     */
    public function create(Service $objService, string $strType): ServicePlan
    {
        /** @var AccountingType */
        $objAccountingType = $this->accountingTypeRepo->findByName("Service." . Util::ucfLabel($strType));
        /** @var AccountingTypeRate */
        $objAccountingTypeRate = $this->accountingTypeRateRepo->findLatestByType($objAccountingType);
        $planAliases = config("constant.soundblock.plans_aliases");
        if (!array_key_exists(Util::ucfLabel($strType), $planAliases)) {
            throw new \Exception("Invalid Plan Type", 400);
        }
        // Create a Service Plan
        $objServicePlan = $this->planRepo->create([
            "service_id"        => $objService->service_id,
            "service_uuid"      => $objService->service_uuid,
            "plan_cost"         => $objAccountingTypeRate->accounting_rate,
            "plan_day"          => now()->day,
            "plan_type"         => $planAliases[Util::ucfLabel($strType)]["name"],
            "flag_active"       => false,
            "version"           => $objAccountingTypeRate->accounting_version
        ]);

        return ($objServicePlan);
    }

    /**
     * @param User $user
     * @param string $planName
     * @param float $planCost
     * @return Service
     * @throws \Exception
     */
    public function update(User $user, string $planName, float $planCost): Service
    {
        /** @var Service $objService */
        $objService = $user->service;

        if (is_null($objService)) {
            throw new \Exception("User's service not found.");
        }

        $objService->plans()->active()->update([
            "flag_active" => false
        ]);

        $objService->plans()->create([
            "plan_uuid" => Util::uuid(),
            "service_uuid" => $objService->service_uuid,
            "plan_cost" => $planCost,
            "plan_day" => now()->day,
            "plan_type" => $planName,
            "flag_active" => true
        ]);

        return $objService;
    }
}

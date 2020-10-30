<?php

namespace App\Http\Controllers\Common;

use Exception;
use App\Models\{
    User,
    Soundblock\ServicePlan,
    Soundblock\Service
};
use App\Services\{
    AuthService,
    Common\ServicePlanService,
    Core\Auth\AuthPermissionService,
    Common\CommonService
};
use App\Contracts\{
    Soundblock\Service\ServicePlanContract,
    Payment\PaymentContract
};
use App\Http\Controllers\Controller;
use App\Http\Requests\Common\Service\{
    UpdatePlanRequest,
    CreatePlanRequest
};
use App\Http\Transformers\{
    ServiceTransformer,
    Soundblock\ServicePlanTransformer
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServicePlanController extends Controller {
    /** @var AuthService */
    protected AuthService $authService;
    /** @var ServicePlanService */
    protected $planService;
    /** @var AuthPermissionService */
    protected AuthPermissionService $authPermService;

    /**
     * @param AuthService $authService
     * @param AuthPermissionService $authPermService
     * @param ServicePlanContract $planService
     */
    public function __construct(AuthService $authService, AuthPermissionService $authPermService, ServicePlanContract $planService) {
        $this->authService = $authService;
        $this->planService = $planService;
        $this->authPermService = $authPermService;
    }

    /**
     * @param Request $objRequest
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function indexForOffice(Request $objRequest) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                if ($objRequest->service) {
                    $arrPlans = $this->planService->findByService($objRequest->service);
                    return ($this->response->collection($arrPlans, new ServicePlanTransformer(["service"])));
                } else {
                    $arrPlans = ServicePlan::paginate(10);
                    return ($this->response->paginator($arrPlans, new ServicePlanTransformer(["service"])));
                }
            } else {
                abort(403, "You have not requried permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @group Payments
     * @bodyParam service_name required string The Service name
     * @bodyParam type string required Type of service plan (Smart or Simple)
     * @bodyParam payment_id string required The id of stripe payment method. Example: pm_****
     *
     * @param CreatePlanRequest $objRequest
     * @param CommonService $commonService
     * @param PaymentContract $payment
     *
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function store(CreatePlanRequest $objRequest, CommonService $commonService, PaymentContract $payment) {
        try {
            // Create a new Service.
            /** @var Service */
            $objService = $commonService->create($objRequest->service_name);
            /** @var User */
            $objUser = Auth::user();
            // Add a paymethod to the user.
            $pm = $payment->getOrCreateCustomer($objUser);
            $payment->addPaymentMethod($objUser, $objRequest->input("payment_id"));
            $objServicePlan = $this->planService->create($objService, $objRequest->type);

            return ($this->response->item($objServicePlan, new ServicePlanTransformer));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @group Payments
     *
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function cancel() {
        try {
            /** @var User $objUser */
            $objUser = \Auth::user();

            $objService = $this->planService->cancel($objUser);

            return $this->response()->item($objService, new ServiceTransformer(["plans"]));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @group Payments
     * @bodyParam type string required Type of service plan (Smart or Simple)
     * @bodyParam payment_id string required The id of stripe payment method. Example: pm_****
     * @bodyParam zip int required Zip Code
     *
     * @param UpdatePlanRequest $objRequest
     * @param PaymentContract $payment
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function update(UpdatePlanRequest $objRequest, PaymentContract $payment) {
        try {
            /** @var User $objUser */
            $objUser = Auth::user();
            $pm = $payment->getOrCreateCustomer($objUser);
            $payment->addPaymentMethod($objUser, $objRequest->input("payment_id"));

            $planAliases = config("constant.soundblock.plans_aliases");

            if (!array_key_exists($objRequest->input("type"), $planAliases)) {
                throw new \Exception("Invalid Plan Type", 400);
            }

            $arrPlanConst = $planAliases[$objRequest->input("type")];
            $objService = $this->planService->update($objUser, $arrPlanConst["name"], $arrPlanConst["price"]);

            return $this->response()->item($objService, new ServiceTransformer(["plans"], true));
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

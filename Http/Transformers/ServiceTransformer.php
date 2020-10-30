<?php

namespace App\Http\Transformers;

use App\Http\Transformers\Soundblock\ServicePlanTransformer;
use App\Http\Transformers\Soundblock\TransactionTransformer;
use App\Http\Transformers\User\UserTransformer;
use App\Models\BaseModel;
use App\Models\Soundblock\Service;
use App\Models\User;
use App\Traits\StampCache;

class ServiceTransformer extends BaseTransformer
{
    /**
     * @var bool
     */
    private $bnPlanActive;
    /**
     * @var bool
     */
    private $bnIncludePayment;

    /**
     * ServiceTransformer constructor.
     * @param array|null $arrIncludes
     * @param bool $bnPlanActive
     * @param bool $bnIncludePayment
     */
    public function __construct(array $arrIncludes = null, $bnPlanActive = false, $bnIncludePayment = false) {
        parent::__construct($arrIncludes, "soundblock");
        $this->bnPlanActive = $bnPlanActive;
        $this->bnIncludePayment = $bnIncludePayment;
    }

    use StampCache;

    public function transform(Service $objService)
    {
        $response = [
            "service_uuid" => $objService->service_uuid,
            "service_name" => $objService->service_name,
            "payment" => []
        ];

        switch($this->resType)
        {
            case "soundblock": {
                break;
            }
            case "office": {
                $response["downloads"] = $objService->downloads();
                break;
            }
            default: break;
        }

        if ($this->bnIncludePayment) {
            /** @var User $user */
            $user = \Auth::user();

            $paymentMethod = $user->defaultPaymentMethod();

            if(isset($paymentMethod)) {
                $arrayPayment = $paymentMethod->asStripePaymentMethod()->toArray();

                $response["payment"] = [
                    "payment_id" => $arrayPayment["id"],
                    "card" => [
                        "brand" => $arrayPayment["card"]["brand"],
                        "exp_month" => $arrayPayment["card"]["exp_month"],
                        "exp_year" => $arrayPayment["card"]["exp_year"],
                        "last4" => $arrayPayment["card"]["last4"],
                        "country" => $arrayPayment["card"]["country"],
                    ]
                ];
            }
        }

        return(array_merge($response, $this->stamp($objService)));
    }

    public function includeUser(Service $objService)
    {
        return($this->item($objService->user, new UserTransformer()));
    }

    public function includeTransactions(Service $objService)
    {
        return($this->collection($objService->transactions, new TransactionTransformer()));
    }

    public function includePlans(Service $objService)
    {
        if($this->bnPlanActive) {
            $objPlan = $objService->plans()->active()->first();

            if(is_null($objPlan)) {
                return null;
            }

            return($this->item($objPlan, new ServicePlanTransformer()));
        }

        return($this->item($objService->plans()->orderBy(BaseModel::STAMP_CREATED, "desc")->first(), new ServicePlanTransformer()));
    }

}

<?php

namespace App\Http\Controllers\Payments;

use App\Services\AuthService;
use App\Http\{Controllers\Controller,
    Requests\Office\Finance\SaveChargeRatesRequest,
    Transformers\Office\Accounting\AccountingTypeTransformer
};
use App\Repositories\Accounting\{AccountingTypeRateRepository, AccountingTypeRepository};

class AccountingController extends Controller {
    /**
     * @var AccountingTypeRepository
     */
    private AccountingTypeRepository $accountingTypeRepository;
    /**
     * @var AccountingTypeRateRepository
     */
    private AccountingTypeRateRepository $accountingTypeRateRepository;
    /**
     * @var AuthService
     */
    private AuthService $authService;

    /**
     * AccountingController constructor.
     * @param AccountingTypeRepository $accountingTypeRepository
     * @param AccountingTypeRateRepository $accountingTypeRateRepository
     * @param AuthService $authService
     */
    public function __construct(AccountingTypeRepository $accountingTypeRepository, AccountingTypeRateRepository $accountingTypeRateRepository,
                                AuthService $authService) {
        $this->accountingTypeRepository = $accountingTypeRepository;
        $this->accountingTypeRateRepository = $accountingTypeRateRepository;
        $this->authService = $authService;
    }

    /**
     * @group Payments
     *
     * @return \Dingo\Api\Http\Response
     */
    public function getCharges() {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if (!$this->authService->checkAuth($reqOffice)) {
                abort(403, "You don't have this permissions.");
            }

            $objAccounting = $this->accountingTypeRepository->all();

            return $this->response->collection($objAccounting, new AccountingTypeTransformer(["accountingTypeRates"]));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @group Payments
     * @bodyParam rates[contract] float required Rate For Contract
     * @bodyParam rates[user] float required Rate For User
     * @bodyParam rates[download] float required Rate For Download
     * @bodyParam rates[upload] float required Rate For Upload
     *
     * @param SaveChargeRatesRequest $objRequest
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function saveCharges(SaveChargeRatesRequest $objRequest) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if (!$this->authService->checkAuth($reqOffice)) {
                abort(403, "You don't have this permissions.");
            }

            $objAccounting = $this->accountingTypeRateRepository->saveRates($objRequest->all());

            return $this->response->collection($objAccounting, new AccountingTypeTransformer(["accountingTypeRates"]));
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

<?php

namespace App\Listeners\Soundblock;

use Auth;
use Util;
use App\Models\BaseModel;
use App\Services\Soundblock\ContractService;

class CreateContractListener {
    protected ContractService $contractService;

    /**
     * Create the event listener.
     *
     * @param ContractService $contractService
     */
    public function __construct(ContractService $contractService) {
        $this->contractService = $contractService;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     * @throws \Exception
     */
    public function handle($event) {
        $objProject = $event->objProject;
        $arrContracts = $event->arrContracts;

        $objContract = $this->contractService->create($objProject);

        foreach ($arrContracts as $contract) {
            $objUser = $contract["user"];
            $objContract->users()->attach($objUser->user_id, [
                "row_uuid"                  => Util::uuid(),
                "contract_uuid"             => $objContract->contract_uuid,
                "user_uuid"                 => $objUser->user_uuid,
                "contract_status"           => $contract["contract_status"],
                "user_payout"               => $contract["user_payout"],
                BaseModel::STAMP_CREATED    => time(),
                BaseModel::STAMP_CREATED_BY => Auth::id(),
                BaseModel::STAMP_UPDATED    => time(),
                BaseModel::STAMP_UPDATED_BY => Auth::id(),
            ]);
        }
    }
}

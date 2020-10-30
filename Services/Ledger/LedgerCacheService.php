<?php

namespace App\Services\Ledger;

use App\Helpers\Util;
use App\Models\Soundblock\Ledger;
use App\Contracts\Soundblock\{Ledger\LedgerCacheContract, LedgerContract};
use App\Repositories\Soundblock\{ContractLedgerRepository, ContractRepository, LedgerRepository};

class LedgerCacheService implements LedgerCacheContract {
    /**
     * @var LedgerContract
     */
    private LedgerContract $ledgerService;
    /**
     * @var LedgerRepository
     */
    private LedgerRepository $ledgerRepository;
    /**
     * @var ContractRepository
     */
    private ContractRepository $contractRepository;
    /**
     * @var ContractLedgerRepository
     */
    private ContractLedgerRepository $contractLedgerRepository;

    /**
     * LedgerCacheService constructor.
     * @param LedgerContract $ledgerService
     * @param LedgerRepository $ledgerRepository
     * @param ContractRepository $contractRepository
     * @param ContractLedgerRepository $contractLedgerRepository
     */
    public function __construct(LedgerContract $ledgerService, LedgerRepository $ledgerRepository,
                                ContractRepository $contractRepository, ContractLedgerRepository $contractLedgerRepository) {
        $this->ledgerService = $ledgerService;
        $this->ledgerRepository = $ledgerRepository;
        $this->contractRepository = $contractRepository;
        $this->contractLedgerRepository = $contractLedgerRepository;
    }

    /**
     * @param Ledger $objLedger
     * @return Ledger
     */
    public function updateLedgerByCache(Ledger $objLedger): Ledger {
        $arrLedgerExist = $this->ledgerService->getDocument($objLedger->qldb_table, $objLedger->qldb_id);

        if(empty($arrLedgerExist["document"])) {
            $ledgerData = $this->ledgerService->insertDocument($objLedger->qldb_table, $objLedger->qldb_data);

            $objLedger->qldb_id = $ledgerData["id"];
            $objLedger->qldb_data = $ledgerData["data"];
        } else {
            $ledgerData = $this->ledgerService->updateDocument($objLedger->qldb_table, $objLedger->qldb_id, $objLedger->qldb_data);

            $objLedger->qldb_id = $ledgerData["rid"];
        }

        $objLedger->qldb_block = $ledgerData["blockAddress"];
        $objLedger->qldb_hash = $ledgerData["hash"];
        $objLedger->qldb_metadata = $ledgerData["metadata"];
        $objLedger->save();

        return $objLedger;
    }

    /**
     * @param string $strTableName
     * @param array $arrData
     * @return Ledger
     */
    public function saveCache(string $strTableName, array $arrData): Ledger {
        switch($strTableName) {
            case "soundblock_contracts":
                $objContract = $this->contractRepository->find($arrData["data"]["contract_detail"]["contract_uuid"]);

                $arrTableFields = [
                    "table_name" => $this->contractLedgerRepository::MYSQL_TABLE,
                    "table_field" => $this->contractLedgerRepository::MYSQL_ID_FIELD,
                    "table_id" => $objContract->contract_id
                ];
                break;
            default:
                throw new \InvalidArgumentException("strTableName is not supported by blockchain service.");
                break;
        }

        $objLedger = $this->ledgerRepository->get($arrData["id"]);

        if(is_null($objLedger)) {
            $arrTableFields["ledger_name"] = env("LEDGER_NAME");
            $arrTableFields["ledger_memo"] = env("LEDGER_NAME");
            $arrTableFields["ledger_uuid"] = Util::uuid();
            $objLedger = $this->ledgerRepository->makeModel($arrTableFields);
        }


        $objLedger->qldb_id = $arrData["id"];
        $objLedger->qldb_table = $strTableName;
        $objLedger->qldb_block = $arrData["blockAddress"];
        $objLedger->qldb_data = $arrData["data"];
        $objLedger->qldb_hash = $arrData["hash"];
        $objLedger->qldb_metadata = $arrData["metadata"];
        $objLedger->save();

        return $objLedger;
    }
}

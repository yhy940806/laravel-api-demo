<?php

namespace App\Repositories\Soundblock;

use App\Contracts\Soundblock\LedgerContract;
use App\Exceptions\LedgerMicroserviceException;
use App\Facades\Exceptions\Disaster;
use App\Models\Soundblock\Ledger;
use App\Models\Soundblock\ServiceTransaction;
use App\Repositories\BaseRepository;

class TransactionLedgerRepository extends BaseRepository {
    /* QLDB Transaction table name */
    const TABLE = "soundblock_contracts";


    /* MySQL Transaction Table */
    const MYSQL_TABLE = "soundblock_contracts";

    /* MySQL Transaction Table Primary Field */
    const MYSQL_ID_FIELD = "contract_id";
    /**
     * @var LedgerContract
     */
    private LedgerContract $ledgerService;
    /**
     * @var LedgerRepository
     */
    private $ledgerRepository;

    /**
     * TransactionLedgerRepository constructor.
     * @param LedgerContract $ledgerService
     * @param LedgerRepository $ledgerRepository
     */
    public function __construct(LedgerContract $ledgerService, LedgerRepository $ledgerRepository) {
        $this->ledgerService = $ledgerService;
        $this->ledgerRepository = $ledgerRepository;
    }

    /**
     * @param ServiceTransaction $serviceTransaction
     * @return array|null
     * @throws \Exception
     */
    public function createDocument(ServiceTransaction $serviceTransaction) {
        try {
            /** @var Ledger $objLedger */
            $objLedger = $serviceTransaction->ledger;

            $data = [
                "transaction_details" => $serviceTransaction->load(["user", "accountingTransaction"])->makeHidden("ledger"),
            ];

            if (isset($objLedger)) {
                $arrLedgerData = $this->ledgerService->updateDocument(self::TABLE, $objLedger->qldb_id, $data);
                $objLedger->qldb_data = $data;
            } else {
                $arrLedgerData = $this->ledgerService->insertDocument(self::TABLE, $data);
                $objLedger = $this->ledgerRepository->create([
                    "ledger_name"   => env("LEDGER_NAME"),
                    "ledger_memo"   => env("LEDGER_NAME"),
                    "qldb_id"       => $arrLedgerData["id"],
                    "qldb_table"    => self::TABLE,
                    "qldb_data"     => $data,
                    "qldb_hash"     => $arrLedgerData["hash"],
                    "qldb_block"    => $arrLedgerData["blockAddress"],
                    "qldb_metadata" => $arrLedgerData["metadata"],
                    "table_name"    => self::MYSQL_TABLE,
                    "table_field"   => self::MYSQL_ID_FIELD,
                    "table_id"      => $serviceTransaction->transaction_id,
                ]);

                $serviceTransaction->ledger()->associate($objLedger);
                $serviceTransaction->ledger_uuid = $objLedger->ledger_uuid;
                $serviceTransaction->save();
            }
        } catch (LedgerMicroserviceException $exception) {
            Disaster::handleDisaster($exception);

            return null;
        }

        return $arrLedgerData;
    }
}

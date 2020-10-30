<?php


namespace App\Repositories\Soundblock;

use App\Facades\Exceptions\Disaster;
use App\Contracts\Soundblock\LedgerContract;
use App\Exceptions\LedgerMicroserviceException;
use App\Models\{Soundblock\Contract, Soundblock\Ledger};

class ContractLedgerRepository {
    /* QLDB Contract table name */
    const TABLE = "soundblock_contracts";

    /* MySQL Contract Table */
    const MYSQL_TABLE = "soundblock_contracts";

    /* MySQL Contract Table Primary Field */
    const MYSQL_ID_FIELD = "contract_id";
    /**
     * @var LedgerContract
     */
    private LedgerContract $ledger;
    /**
     * @var LedgerRepository
     */
    private LedgerRepository $ledgerRepo;

    /**
     * ContractLedgerRepository constructor.
     * @param LedgerContract $ledger
     * @param LedgerRepository $ledgerRepo
     */
    public function __construct(LedgerContract $ledger, LedgerRepository $ledgerRepo) {
        $this->ledger = $ledger;
        $this->ledgerRepo = $ledgerRepo;
    }

    /**
     * @param Contract $contract
     * @param string $actionText
     * @return array|null
     */
    public function createDocument(Contract $contract, string $actionText) : ?array{
        try{
            /** @var Ledger $objLedger*/
            $objLedger = $contract->ledger;

            $data = [
                "contract" => $actionText,
                "contract_detail" => $contract->load(["users", "project", "invites"])->makeHidden("ledger")
            ];

            if (isset($objLedger)) {
                $arrLedgerData = $this->ledger->updateDocument(self::TABLE, $objLedger->qldb_id, $data);
                $objLedger->qldb_data = $data;
            } else {
                $arrLedgerData = $this->ledger->insertDocument(self::TABLE, $data);
                $objLedger = $this->ledgerRepo->create([
                    "ledger_name" => env("LEDGER_NAME"),
                    "ledger_memo" => env("LEDGER_NAME"),
                    "qldb_id" => $arrLedgerData["id"],
                    "qldb_table" => self::TABLE,
                    "qldb_data" => $data,
                    "qldb_hash" => $arrLedgerData["hash"],
                    "qldb_block" => $arrLedgerData["blockAddress"],
                    "qldb_metadata" => $arrLedgerData["metadata"],
                    "table_name" => self::MYSQL_TABLE,
                    "table_field" => self::MYSQL_ID_FIELD,
                    "table_id" => $contract->contract_id
                ]);

                $contract->ledger()->associate($objLedger);
                $contract->ledger_uuid = $objLedger->ledger_uuid;
                $contract->save();
            }
        } catch(LedgerMicroserviceException $exception) {
            Disaster::handleDisaster($exception);

            return null;
        }

        return $arrLedgerData;
    }
}

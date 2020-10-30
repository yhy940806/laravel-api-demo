<?php

namespace App\Repositories\Soundblock;

use App\Models\Soundblock\Ledger;
use App\Repositories\BaseRepository;

class LedgerRepository extends BaseRepository {

    /**
     * LedgerRepository constructor.
     * @param Ledger $ledger
     */
    public function __construct(Ledger $ledger) {
        $this->model = $ledger;
    }

    /**
     * @param string $qldbId
     * @return Ledger|null
     */
    public function get(string $qldbId) : ?Ledger {
        return $this->model->where("qldb_id", $qldbId)->first();
    }

    /**
     * @param array $arrData
     * @return Ledger
     */
    public function makeModel($arrData = []) {
        return  $this->model->newInstance($arrData);
    }
}

<?php

namespace App\Contracts\Soundblock\Ledger;

use App\Models\Soundblock\Ledger;

interface LedgerCacheContract {
    public function updateLedgerByCache(Ledger $objLedger) : Ledger;
    public function saveCache(string $strTableName, array $arrData) : Ledger;
}

<?php

namespace App\Jobs\Soundblock\Ledger;

use Illuminate\Bus\Queueable;
use App\Models\Soundblock\Ledger;
use App\Services\Ledger\LedgerCacheService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use App\Contracts\Soundblock\{Ledger\LedgerCacheContract, LedgerContract};

class UpdateLedgerJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Ledger
     */
    private Ledger $ledger;
    /**
     * @var LedgerContract
     */
    private LedgerContract $ledgerHttp;
    /**
     * @var LedgerCacheContract
     */
    private LedgerCacheContract $ledgerCache;

//    /**
//     * Init a ledger job instance.
//     *
//     * @param Ledger $ledger
//     * @param LedgerContract $ledgerHttp
//     * @param LedgerCacheContract $ledgerCache
//     */
//    public function __construct(Ledger $ledger,) {
//        $this->ledger = $ledger;
//        $this->ledgerHttp = $ledgerHttp;
//        $this->ledgerCache = $ledgerCache;
//    }

    /**
     * Execute the job.
     *
     * @param Ledger $ledger
     * @param LedgerCacheService $ledgerCacheService
     * @param LedgerContract $ledgerContract
     * @return void
     */
    public function handle(Ledger $ledger, LedgerCacheService $ledgerCacheService, LedgerContract $ledgerContract) {
        $objLedgers = $ledger->all();

//        $tables = $this->ledgerHttp->getTablesList();
    }
}

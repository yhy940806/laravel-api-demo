<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Contracts\Soundblock\{Ledger\LedgerCacheContract, LedgerContract};

class LedgerConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ledger:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * @var LedgerContract
     */
    private LedgerContract $ledgerHttp;
    /**
     * @var LedgerCacheContract
     */
    private LedgerCacheContract $ledgerCache;

    /**
     * Create a new command instance.
     *
     * @param LedgerContract $ledgerHttp
     * @param LedgerCacheContract $ledgerCache
     */
    public function __construct(LedgerContract $ledgerHttp, LedgerCacheContract $ledgerCache) {
        parent::__construct();
        $this->ledgerHttp = $ledgerHttp;
        $this->ledgerCache = $ledgerCache;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $arrTables = $this->ledgerHttp->getTablesList();

        foreach($arrTables as $strTable) {
            $arrTableDocuments = $this->ledgerHttp->getTableDocuments($strTable);

            foreach($arrTableDocuments as $arrTableDocument) {
                $this->ledgerCache->saveCache($strTable, $arrTableDocument);
            }
        }
    }
}

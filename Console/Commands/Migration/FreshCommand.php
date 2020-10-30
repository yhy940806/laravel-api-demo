<?php

namespace App\Console\Commands\Migration;

use App\Contracts\Soundblock\LedgerContract;
use App\Exceptions\LedgerMicroserviceException;
use App\Facades\Exceptions\Disaster;
use Illuminate\Database\Console\Migrations\FreshCommand as BaseFreshCommand;

class FreshCommand extends BaseFreshCommand
{
    /**
     * @var LedgerContract
     */
    private LedgerContract $ledger;

    /**
     * Create a new command instance.
     *
     * @param LedgerContract $ledger
     */
    public function __construct(LedgerContract $ledger) {
        $this->ledger = $ledger;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        try{
            $this->ledger->deleteAllTables();
        } catch(LedgerMicroserviceException $exception) {
            Disaster::handleDisaster($exception);
        }

        return parent::handle();
    }
}

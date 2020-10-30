<?php

namespace App\Listeners\Accounting;

use App\Services\Accounting\AccountingInvoiceTransactionService;

class CreateTransactionListener {
    /**
     * @param AccountingInvoiceTransactionService
     */
    protected AccountingInvoiceTransactionService $transactionService;

    /**
     * Create the event listener.
     * @param AccountingInvoiceTransactionService $transactionService
     * @return void
     */
    public function __construct(AccountingInvoiceTransactionService $transactionService) {
        $this->transactionService = $transactionService;
    }

    /**
     * Handle the event.
     *
     * @param \App\Events\Accounting\CreateTransaction $event
     * @return void
     */
    public function handle($event) {
        $this->transactionService->create($event->objInvoice, $event->instance, $event->arrOptions, $event->objOfficeUser);
    }
}

<?php

namespace App\Services\Accounting;

use App\Models\User;
use App\Models\Accounting\{
    AccountingInvoice,
    AccountingInvoiceTransaction
};
use Illuminate\Database\Eloquent\Model;
use App\Contracts\Accounting\InvoiceTransactionContract;
use App\Repositories\Accounting\AccountingInvoiceTransactionRepository;

class AccountingInvoiceTransactionService implements InvoiceTransactionContract {

    /**
     * @var AccountingInvoiceTransactionRepository
     */
    protected AccountingInvoiceTransactionRepository $invoiceTransactionRepo;

    /**
     * @param AccountingInvoiceTransactionRepository $invoiceTransactionRepo
     */
    public function __construct(AccountingInvoiceTransactionRepository $invoiceTransactionRepo) {
        $this->invoiceTransactionRepo = $invoiceTransactionRepo;
    }

    /**
     * @param AccountingInvoice $objInvoice
     * @param Model $instance
     * @param array $arrOptions
     * @param User|null $objOfficeUser
     * @return AccountingInvoiceTransaction
     * @throws \Exception
     */
    public function create(AccountingInvoice $objInvoice, Model $instance, array $arrOptions, ?User $objOfficeUser = null): AccountingInvoiceTransaction {
        return ($this->invoiceTransactionRepo->createTransaction($objInvoice, $instance, $arrOptions, $objOfficeUser));
    }
}

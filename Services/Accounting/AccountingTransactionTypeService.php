<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingTransactionType;
use App\Repositories\Accounting\AccountingTransactionTypeRepository;

class AccountingTransactionTypeService {

    /**
     * @var AccountingTransactionTypeRepository
     */
    protected AccountingTransactionTypeRepository $transactionTypeRepo;

    /**
     * @param AccountingTransactionTypeRepository $transactionTypeRepo
     *
     * @return void
     */
    public function __construct(AccountingTransactionTypeRepository $transactionTypeRepo) {
        $this->transactionTypeRepo = $transactionTypeRepo;
    }

    /**
     * @param string $typeName
     *
     * @return AccountingTransactionType
     */
    public function findByName(string $typeName): AccountingTransactionType {
        return ($this->transactionTypeRepo->findByName($typeName));
    }
}

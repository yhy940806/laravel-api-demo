<?php

namespace App\Services\Common;

use App\Models\Accounting\AccountingType;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Accounting\AccountingTypeRepository;

class AccountingTypeService {

    /**
     * @var AccountingTypeRepository
     */
    protected AccountingTypeRepository $accountingTypeRepo;

    /**
     * @param AccountingTypeRepository $accountingTypeRepo
     *
     * @return void
     */
    public function __construct(AccountingTypeRepository $accountingTypeRepo) {
        $this->accountingTypeRepo = $accountingTypeRepo;
    }

    /**
     * @return Collection
     */
    public function findAll(): Collection {
        return ($this->accountingTypeRepo->all());
    }

    /**
     * @param string $accountingTypeName
     *
     * @return AccountingType
     */
    public function findByName(string $accountingTypeName): AccountingType {
        return ($this->accountingTypeRepo->findByName($accountingTypeName));
    }
}

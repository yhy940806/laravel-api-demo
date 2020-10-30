<?php

namespace App\Repositories\Accounting;

use App\Repositories\BaseRepository;
use App\Models\Accounting\AccountingType;

class AccountingTypeRepository extends BaseRepository {
    /**
     * AccountingTypeRepository constructor.
     * @param AccountingType $accountingType
     */
    public function __construct(AccountingType $accountingType) {
        $this->model = $accountingType;
    }

    public function findByName(string $accountingTypeName): ?AccountingType {
        return $this->model->whereRaw("lower(accounting_type_name) = ?", strtolower($accountingTypeName))->first();
    }
}

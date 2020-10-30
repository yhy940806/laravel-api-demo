<?php

namespace App\Repositories\Accounting;

use App\Repositories\BaseRepository;
use App\Models\Accounting\AccountingTransactionType;

class AccountingTransactionTypeRepository extends BaseRepository {
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected \Illuminate\Database\Eloquent\Model $model;

    /**
     * @param AccountingTransactionType $model
     *
     * @return void
     */
    public function __construct(AccountingTransactionType $model) {
        $this->model = $model;
    }

    /**
     * @param string $typeName
     *
     * @return AccountingTransactionType
     */
    public function findByName(string $typeName): AccountingTransactionType {
        return ($this->model->whereRaw("lower(type_name) = (?)", strtolower($typeName))->firstOrFail());
    }

    /**
     * @param string $typeCode
     *
     * @return AccountingTransactionType
     */
    public function findByCode(string $typeCode): AccountingTransactionType {
        return ($this->model->where("type_code", $typeCode)->firstOrFail());
    }
}

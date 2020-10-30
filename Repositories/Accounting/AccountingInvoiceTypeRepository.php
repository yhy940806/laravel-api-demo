<?php

namespace App\Repositories\Accounting;

use App\Repositories\BaseRepository;
use App\Models\Accounting\AccountingInvoiceType;

class AccountingInvoiceTypeRepository extends BaseRepository {
    protected \Illuminate\Database\Eloquent\Model $model;

    /**
     * @param AccountingInvoiceType $model
     *
     * @return void
     */
    public function __construct(AccountingInvoiceType $model) {
        $this->model = $model;
    }

    /**
     * @param string $typeCode
     *
     * @return AccountingInvoiceType
     */
    public function findByCode(string $typeCode): AccountingInvoiceType {
        return ($this->model->where("type_code", $typeCode)->firstOrFail());
    }

    public function findByName(string $typeName): AccountingInvoiceType {
        return ($this->model->whereRaw("lower(type_name) = (?)", strtolower($typeName))->firstOrFail());
    }
}

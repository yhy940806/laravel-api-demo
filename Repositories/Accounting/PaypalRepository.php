<?php

namespace App\Repositories\Accounting;

use App\Models\AccountingPaypal;
use App\Repositories\BaseRepository;

class PaypalRepository extends BaseRepository {

    protected \Illuminate\Database\Eloquent\Model $model;

    public function __construct(AccountingPaypal $objPaypal) {
        $this->model = $objPaypal;
    }
}

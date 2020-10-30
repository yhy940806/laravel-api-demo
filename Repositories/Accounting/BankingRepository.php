<?php

namespace App\Repositories\Accounting;

use App\Models\AccountingBanking;
use App\Repositories\BaseRepository;

class BankingRepository extends BaseRepository {

    protected \Illuminate\Database\Eloquent\Model $model;

    public function __construct(AccountingBanking $objBanking) {
        $this->model = $objBanking;
    }
}

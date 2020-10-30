<?php


namespace App\Repositories\Accounting;

use App\Helpers\Util;
use App\Repositories\BaseRepository;
use App\Models\{Core\App, Accounting\AccountingTypeRate, Accounting\AccountingTransaction, Soundblock\ServiceTransaction};

class AccountingTransactionRepository extends BaseRepository {
    /**
     * AccountingTransactionRepository constructor.
     * @param AccountingTransaction $accountingTransaction
     */
    public function __construct(AccountingTransaction $accountingTransaction) {
        $this->model = $accountingTransaction;
    }

    public function createAccountingTransaction(ServiceTransaction $serviceTransaction, AccountingTypeRate $accountingTypeRate, App $app) {
        return $serviceTransaction->accountingTransaction()->create([
            "transaction_uuid"   => Util::uuid(),
            "app_id"             => $app->app_id,
            "app_uuid"           => $app->app_uuid,
            "app_table"          => $serviceTransaction->getTable(),
            "app_field"          => $serviceTransaction->getKeyName(),
            "app_field_id"       => $serviceTransaction->row_id,
            "app_field_uuid"     => $serviceTransaction->row_uuid,
            "transaction_amount" => $accountingTypeRate->accounting_rate,
            "transaction_name"   => ucfirst($app->app_name) . " Transaction",
            "transaction_memo"   => ucfirst($app->app_name) . " Transaction",
        ]);
    }
}

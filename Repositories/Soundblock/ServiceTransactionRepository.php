<?php

namespace App\Repositories\Soundblock;

use App\Helpers\Util;
use App\Repositories\BaseRepository;
use App\Models\{Accounting\AccountingType, Soundblock\Service, Soundblock\ServiceTransaction};

class ServiceTransactionRepository extends BaseRepository {
    /**
     * ServiceTransactionRepository constructor.
     * @param ServiceTransaction $serviceTransaction
     */
    public function __construct(ServiceTransaction $serviceTransaction) {
        $this->model = $serviceTransaction;
    }

    /**
     * @param Service $service
     * @param AccountingType $accountingType
     * @return ServiceTransaction
     * @throws \Exception
     */
    public function createTransaction(Service $service, AccountingType $accountingType): ServiceTransaction {
        return $service->transactions()->create([
            "row_uuid" => Util::uuid(),
            "service_uuid"     => $service->service_uuid,
            "ledger_id"        => rand(10, 100000),
            "ledger_uuid"      => Util::uuid(),
            "accounting_type_id"   => $accountingType->accounting_type_id,
            "accounting_type_uuid" => $accountingType->accounting_type_uuid,
        ]);
    }
}

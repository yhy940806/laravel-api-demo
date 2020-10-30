<?php

namespace App\Repositories\Accounting;

use App\Helpers\Util;
use Illuminate\Support\Facades\DB;
use App\Repositories\BaseRepository;
use App\Models\Accounting\{AccountingType, AccountingTypeRate};

class AccountingTypeRateRepository extends BaseRepository {
    /**
     * @var AccountingTypeRate
     */
    protected \Illuminate\Database\Eloquent\Model $model;
    /**
     * @var AccountingTypeRepository
     */
    private AccountingTypeRepository $accountingTypeRepository;

    /**
     * AccountingTypeRateRepository constructor.
     * @param AccountingTypeRate $accountingTypeRate
     * @param AccountingTypeRepository $accountingTypeRepository
     */
    public function __construct(AccountingTypeRate $accountingTypeRate, AccountingTypeRepository $accountingTypeRepository) {
        $this->model = $accountingTypeRate;
        $this->accountingTypeRepository = $accountingTypeRepository;
    }

    /**
     * @param AccountingType $accountingType
     * @param int $version
     * @return AccountingTypeRate
     */
    public function getRateByVersion(AccountingType $accountingType, int $version): AccountingTypeRate {
        return $accountingType->accountingTypeRates()->where("accounting_version", $version)->first();
    }

    public function saveRates(array $arrParams) {
        $version = $this->getLastRateVersion() + 1;

        if (isset($arrParams["rates"])) {
            DB::beginTransaction();

            foreach ($arrParams["rates"] as $rateTypeName => $rate) {
                $objAccounting = $this->accountingTypeRepository->findByName($rateTypeName);

                if (is_null($objAccounting)) {
                    continue;
                }

                $objAccounting->accountingTypeRates()->create([
                    "row_uuid"             => Util::uuid(),
                    "accounting_type_id"   => $objAccounting->accounting_type_id,
                    "accounting_type_uuid" => $objAccounting->accounting_type_uuid,
                    "accounting_version"   => $version,
                    "accounting_rate"      => $rate,
                ]);
            }

            DB::commit();
        }


        return $this->accountingTypeRepository->all();
    }

    public function getLastRateVersion(): int {
        return $this->model->max("accounting_version");
    }

    /**
     * @param AccountingType $objAccountingType
     *
     * @return AccountingTypeRate
     */
    public function findLatestByType(AccountingType $objAccountingType): AccountingTypeRate {
        return ($objAccountingType->accountingTypeRates()->orderBy("accounting_version", "desc")->firstOrFail());
    }
}

<?php

namespace App\Repositories\Accounting;

use Util;
use Auth;
use Exception;
use App\Models\{
    BaseModel,
    Core\App,
    User,
    Accounting\AccountingInvoice,
    Accounting\AccountingInvoiceType,
    Soundblock\Service,
    Soundblock\ServiceTransaction
};
use Carbon\Carbon;
use Laravel\Cashier\PaymentMethod;
use App\Repositories\BaseRepository;

class AccountingInvoiceRepository extends BaseRepository {
    /**
     * AccountingInvoiceRepository constructor.
     * @param AccountingInvoice $accountingInvoice
     */
    public function __construct(AccountingInvoice $accountingInvoice) {
        $this->model = $accountingInvoice;
    }

    /**
     * @param array $options
     * @param int $perPage
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\Paginator
     */
    public function findAll(array $options = [], ?int $perPage = null) {
        /** @var \Illuminate\Database\Eloquent\Builder */
        $query = $this->model->with(["invoiceType.app", "transactions.transactionType"])
                             ->join("core_apps", "accounting_invoices.app_id", "=", "core_apps.app_id")
                             ->join("accounting_types_invoices", "accounting_invoices.invoice_type", "=", "accounting_types_invoices.type_id");
        $query = $this->applyFilter($query, $options);
        if ($perPage) {
            return ($query->select("accounting_invoices.*")->paginate($perPage));
        } else {
            return ($query->select("accounting_invoices.*")->get());
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyFilter($query, array $options) {
        if (isset($options["sort_invoice_type"])) {
            $query->orderBy("accounting_types_invoices.type_name", Util::lowerLabel($options["sort_invoice_type"]));
        }
        if (isset($options["sort_app"])) {
            $query->orderBy("core_apps.app_name", Util::lowerLabel($options["sort_app"]));
        }

        return ($query);
    }

    /**
     * @param Service $service
     * @param PaymentMethod $paymentMethod
     * @param float $amount
     * @param App $app
     * @return AccountingInvoice
     * @throws \Exception
     */
    public function storeInvoice(Service $service, PaymentMethod $paymentMethod, float $amount, App $app): AccountingInvoice {
        $objUser = $service->user;

        /** @var AccountingInvoice $invoice */
        $invoice = $this->model->create([
            "invoice_uuid"   => Util::uuid(),
            "app_id"         => $app->app_id,
            "app_uuid"       => $app->app_uuid,
            "user_id"        => $objUser->user_id,
            "user_uuid"      => $objUser->user_uuid,
            "invoice_date"   => Carbon::now(),
            "invoice_amount" => $amount,
            "invoice_status" => "paid",
        ]);

        $transactions = $service->transactions;

        /** @var ServiceTransaction $transaction */
        foreach ($transactions as $transaction) {
            $invoice->serviceTransactions()->attach($transaction->row_id, [
                "invoice_uuid"     => $invoice->invoice_uuid,
                "transaction_uuid" => $transaction->row_uuid,
            ]);
        }

        return $invoice;
    }

    /**
     * @param User $objUser
     * @param AccountingInvoiceType $objInvoiceType
     * @param App $objApp
     * @param array $arrOptions
     * @return AccountingInvoice
     */
    public function createInvoiceFor(User $objUser, AccountingInvoiceType $objInvoiceType, App $objApp, array $arrOptions = []): AccountingInvoice {
        if (!array_key_exists("payment_response", $arrOptions) || !$objUser->stripe)
            throw new Exception("Invalid Parameter.", 400);
        $model = $this->model->newInstance();

        $arrParams = [
            "invoice_uuid"     => Util::uuid(),
            "app_id"           => $objApp->app_id,
            "app_uuid"         => $objApp->app_uuid,
            "user_id"          => $objUser->user_id,
            "user_uuid"        => $objUser->user_uuid,
            "invoice_type"     => $objInvoiceType->type_id,
            "invoice_date"     => Carbon::now(),
            "invoice_amount"   => $arrOptions["payment_response"]["total"],
            "invoice_status"   => $arrOptions["payment_response"]["status"],
            "invoice_discount" => isset($arrOptions["discount"]) ? intval($arrOptions["discount"]) : null,
            "invoice_coupon"   => isset($arrOptions["coupon"]) ? $arrOptions["coupon"] : null,
        ];
        if (isset($arrOptions["discount"])) {
            $arrParams = array_merge($arrParams, [
                BaseModel::DISCOUNT_AT       => now(),
                BaseModel::STAMP_DISCOUNT    => time(),
                BaseModel::STAMP_DISCOUNT_BY => Auth::id(),
            ]);
        }
        $model = $model->create($arrParams);

        $model->payment()->attach($objUser->stripe->row_id, [
            "row_uuid"                  => Util::uuid(),
            "payment_uuid"              => $objUser->stripe->row_uuid,
            "invoice_uuid"              => $model->invoice_uuid,
            "payment_response"          => $arrOptions["payment_response"],
            "payment_status"            => $arrOptions["payment_response"]["status"],
            BaseModel::STAMP_CREATED    => time(),
            BaseModel::STAMP_CREATED_BY => Auth::id(),
            BaseModel::STAMP_UPDATED    => time(),
            BaseModel::STAMP_UPDATED_BY => Auth::id(),
        ]);

        return ($model);
    }

    /**
     * @param string $strUserUUID
     * @param int $perPage
     * @return mixed
     */
    public function getUserInvoices(string $strUserUUID, int $perPage) {
        $objInvoices = $this->model->where("user_uuid", $strUserUUID)->paginate($perPage);

        return ($objInvoices);
    }

    public function getInvoiceByUUID(string $strInvoiceUUID) {
        $objInvoice = $this->model->where("invoice_uuid", $strInvoiceUUID)->first();

        return ($objInvoice);
    }
}

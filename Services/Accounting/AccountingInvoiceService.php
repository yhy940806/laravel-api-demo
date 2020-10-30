<?php

namespace App\Services\Accounting;

use Exception;
use App\Models\{Core\App, User, Accounting\AccountingInvoice, Accounting\AccountingInvoiceType};
use Illuminate\Support\Collection;
use Stripe\Coupon as StripeCoupon;
use Illuminate\Database\Eloquent\Model;
use Stripe\InvoiceItem as StripeInvoiceItem;
use App\Contracts\Accounting\InvoiceContract;
use App\Models\Accounting\AccountingTransactionType;
use App\Repositories\Accounting\{
    AccountingInvoiceRepository,
    AccountingInvoiceTransactionRepository,
    AccountingInvoiceTypeRepository,
    AccountingTransactionTypeRepository
};
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class AccountingInvoiceService implements InvoiceContract {

    /**
     * @var AccountingInvoiceRepository
     */
    protected AccountingInvoiceRepository $invoiceRepo;
    /**
     * @var AccountingInvoiceTypeRepository
     */
    protected AccountingInvoiceTypeRepository $invoiceTypeRepo;
    /**
     * @var AccountingInvoiceTransactionRepository
     */
    protected AccountingInvoiceTransactionRepository $invoiceTransactionRepo;
    /**
     * @var AccountingTransactionTypeRepository
     */
    protected AccountingTransactionTypeRepository $transactionTypeRepo;

    /**
     * @param AccountingInvoiceRepository $invoiceRepo
     * @param AccountingInvoiceTypeRepository $invoiceTypeRepo
     * @param AccountingInvoiceTransactionRepository $invoiceTransactionRepo
     * @param AccountingTransactionTypeRepository $transactionTypeRepo
     *
     * @return void
     */
    public function __construct(AccountingInvoiceRepository $invoiceRepo, AccountingInvoiceTypeRepository $invoiceTypeRepo, AccountingInvoiceTransactionRepository $invoiceTransactionRepo, AccountingTransactionTypeRepository $transactionTypeRepo) {
        $this->invoiceRepo = $invoiceRepo;
        $this->invoiceTypeRepo = $invoiceTypeRepo;
        $this->invoiceTransactionRepo = $invoiceTransactionRepo;
        $this->transactionTypeRepo = $transactionTypeRepo;
    }

    /**
     * @param array $options
     * @param int $perPage
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\Paginator
     */
    public function findAll(array $options = [], ?int $perPage = null) {
        return ($this->invoiceRepo->findAll($options, $perPage));
    }

    /**
     * @param User $objUser
     * @param App $objApp
     * @param array $options = []
     *      $options = [
     *          'coupon'    => (string) Unique identifier for the object.
     *      ]
     *
     * @return AccountingInvoice
     */
    public function createInvoice(User $objUser, App $objApp, AccountingInvoiceType $objInvoiceType, array $arrLineItem, array $options = []): AccountingInvoice {
        /** @var array */
        $arrOptions = [];
        if (isset($options["coupon"])) {
            $objUser->applyCoupon($options["coupon"]);
        }
        $options = (new Collection($options))->except("coupon")->toArray();
        $cashierInvoice = $objUser->invoice($options);
        if (!$cashierInvoice)
            throw new Exception("Stripe Invalid Request Exception", 400);

        $arrOptions["payment_response"] = $cashierInvoice->asStripeInvoice();

        return ($this->invoiceRepo->createInvoiceFor($objUser, $objInvoiceType, $objApp, $arrLineItem, $arrOptions));
    }

    /**
     * @param User $objReceiver
     * @param Model $transaction
     * @param App $objApp
     * @param string $invoiceType
     * @param array $arrLineItem
     * @param User $officeUser
     * @param array $options
     *
     * @return AccountingInvoice
     * @throws \Laravel\Cashier\Exceptions\PaymentActionRequired
     * @throws \Laravel\Cashier\Exceptions\PaymentFailure
     */
    public function createInvoiceFor(User $objReceiver, Model $transaction, App $objApp, string $invoiceType, array $arrLineItem, User $officeUser, array $options = []): AccountingInvoice {
        /** @var array */
        $arrOptions = [];

        if (isset($options["coupon"])) {
            $objReceiver->applyCoupon($options["coupon"]);
        }
        $optionsForStripeInvoice = (new Collection($options))->except(["coupon", "discount"])->toArray();
        $optionForCouponAndDiscount = (new Collection($options))->only(["coupon", "discount"])->toArray();
        $cashierInvoice = $objReceiver->invoice($optionsForStripeInvoice);
        if (!$cashierInvoice)
            throw new Exception("Stripe Invalid Request Exception", 400);

        $arrOptions["payment_response"] = $cashierInvoice->asStripeInvoice();
        $objInvoiceType = $this->invoiceTypeRepo->findByName($invoiceType);
        $arrOptions = array_merge($arrOptions, $optionForCouponAndDiscount);
        $objInvoice = $this->invoiceRepo->createInvoiceFor($objReceiver, $objInvoiceType, $objApp, $arrOptions);

        foreach ($arrLineItem as $lineItem) {
            $objTransactionType = $this->transactionTypeRepo->findByName($lineItem["transaction_type"]);
            $this->invoiceTransactionRepo->createTransaction($objInvoice, $transaction, $objTransactionType, $lineItem, $officeUser);
        }

        return ($objInvoice);
    }

    /**
     * @param User $objUser
     * @param string $name
     * @param string $duration
     * @param float $off
     * @param bool $isPercentage
     * @param array $options
     *
     * @return StripeCoupon
     */
    public function createCoupon(User $objUser, string $name, string $duration, float $off, bool $isPercentage = true, array $options = []): StripeCoupon {
        return ($objUser->createCoupon($name, $duration, $off, $isPercentage, $options));
    }

    /**
     * @param User $objUser
     * @param string $description An arbitary string which you can attach to the invoice item. The description is displayed in the invoice for easy tracking.
     * @param int $unitAmount The integer unit amount in paise of the charge to be applied to the upcoming invoice. This $unitAmount will be multiplied by the quantity to get the full amount.
     * @param int $quantity Non-negative integer. The quantity of units for the invoice item.
     * @param int $discount Non-negative integer. The discount to apply to this invoice item.
     * @param array $options Array containing the necessary params
     *      $options = [
     *          'currency'          => (string) Three-letter ISO currency code, in lowercase Optional.
     *          'tax_rates'         => The tax rates which apply to the invoice item. When set, the default_tax_rates on the invoice do not apply to this invoice item Optional.
     *      ]
     *
     * @return StripeInvoiceItem
     */
    public function createInvoiceItem(User $objUser, string $description, int $unitAmount, int $quantity, int $discount, int $totalDiscount = 0, array $options = []): StripeInvoiceItem {
        return ($objUser->createInvoiceItem($description, $unitAmount, $quantity, $discount, $totalDiscount, $options));
    }

    /**
     * @return EloquentCollection
     */
    public function getInvoiceTypes(): EloquentCollection {
        return ($this->invoiceTypeRepo->all());
    }

    /**
     * @param mixed $id
     * @param bool $bnFailure
     *
     * @return AccountingInvoiceType
     */
    public function findInvoiceType($id, bool $bnFailure = true): AccountingInvoiceType {
        return ($this->invoiceTypeRepo->find($id, $bnFailure));
    }

    /**
     * @param string $typeName
     *
     * @return AccountingInvoiceType
     */
    public function findInvoiceTypeByName(string $typeName): AccountingInvoiceType {
        return ($this->invoiceTypeRepo->findByName($typeName));
    }

    /**
     * @param string $typeCode
     *
     * @return AccountingInvoiceType
     */
    public function findInvoiceTypeByCode(string $typeCode): AccountingInvoiceType {
        return ($this->invoiceTypeRepo->findByCode($typeCode));
    }

    /**
     * @param string $typeName
     *
     * @return AccountingTransactionType
     */
    public function findTransactionTypeByName(string $typeName): AccountingTransactionType {
        return ($this->transactionTypeRepo->findByName($typeName));
    }
}

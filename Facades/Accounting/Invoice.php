<?php

namespace App\Facades\Accounting;

use App\Models\Core\App;
use App\Models\User;
use App\Models\Accounting\{
    AccountingInvoice,
    AccountingInvoiceType
};
use Illuminate\Support\Facades\Facade;
use Stripe\Coupon as StripeCoupon;
use Stripe\InvoiceItem as StripeInvoiceItem;

/**
 * @method static AccountingInvoice createInvoiceFor1(User $objUser, App $objApp, string $description, float $amount, ?array $arrInvoiceOptions = null)
 * @method static AccountingInvoice createInvoiceFor(User $objReceiver, Model $transaction, App $objApp, string $invoiceType, array $arrLineItem, User $officeUser, array $options = [])
 * @method static AccountingInvoice createInvoice(User $objUser, App $objApp, AccountingInvoiceType $objInvoiceType, array $arrLineItem, array $options = [])
 * @method static StripeCoupon createCoupon(User $objUser, string $name, string $duration, float $off, bool $isPercentage = true, array $options = [])
 * @method static StripeInvoiceItem createInvoiceItem(User $objUser, string $description, int $unitAmount, int $quantity, int $discount, int $totalDiscount, array $options = [])
 * @method static AccountingInvoiceType findInvoiceType($id, bool $bnFilaure = true)
 * @method static AccountingInvoiceType findInvoiceTypeByName(string $typeName)
 * @method static AccountingInvoiceType findInvoiceTypeByCode(string $typeCode)
 */
class Invoice extends Facade {

    protected static function getFacadeAccessor() {
        return ("invoice");
    }
}

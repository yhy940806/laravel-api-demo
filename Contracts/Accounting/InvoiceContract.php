<?php

namespace App\Contracts\Accounting;

use App\Models\{
    Core\App,
    User,
    Accounting\AccountingInvoice,
    Accounting\AccountingInvoiceType
};
use Stripe\Coupon as StripeCoupon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Stripe\InvoiceItem as StripeInvoiceItem;

interface InvoiceContract
{
    public function createInvoiceFor(User $objReceiver, Model $transaction, App $objApp, string $invoiceType, array $arrLineItem, User $officeUser, array $options = []): AccountingInvoice;
    public function createInvoice(User $objUser, App $objApp, AccountingInvoiceType $objInvoiceType, array $arrItemsMeta, array $options = []): AccountingInvoice;
    public function createCoupon(User $objUser, string $name, string $duration, float $off, bool $isPercentage = true, array $options = []): StripeCoupon;
    public function createInvoiceItem(User $objUser, string $description, int $unitAmount, int $quantity, int $discount, int $totalDiscount, array $options = []): StripeInvoiceItem;
    public function getInvoiceTypes(): Collection;
    public function findInvoiceType($id, bool $bnFailure = true): AccountingInvoiceType;
    public function findInvoiceTypeByName(string $typeName): AccountingInvoiceType;
    public function findInvoiceTypeByCode(string $typeCode): AccountingInvoiceType;
}

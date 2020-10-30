<?php

namespace App\Traits;

use Laravel\Cashier\Billable;
use Stripe\Coupon as StipeCoupon;
use Stripe\InvoiceItem as StripeInvoiceItem;
use App\Models\{BaseModel, Accounting\AccountingSubscription};

trait FinanceBillable {
    use Billable;

    /**
     * Get all of the subscriptions for the Stripe model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions() {
        return ($this->hasMany(AccountingSubscription::class, $this->getForeignKey()))->orderBy(BaseModel::CREATED_AT, 'desc');
    }

    /**
     * @param string $name
     * @param string $duration
     * @param float $off
     * @param bool $isPercentage
     * @param array $options Array containing the necessary params
     *      $options = [
     *          'percentage_off'        => (float) A positive float larger than 0 and smaller or equal to 100, that represents the discount the coupon will apply Optional.
     *          'amount_off'            => (integer) A positive integer representing the amount to subtract from an invoice total (required if percent_off)
     *          'duration_in_month'     => (integer) Required only if duration is repeating, in which case it must be a positive integer
     *          'metadata'              => (array) Set of key-value pairs that you can attach to an object.
     *      ]
     *
     * @return StipeCoupon
     * @throws \Laravel\Cashier\Exceptions\InvalidStripeCustomer
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createCoupon(string $name, string $duration, float $off, bool $isPercentage = true, array $options = []): StipeCoupon {
        $this->assertCustomerExists();

        if ($isPercentage) {
            $options["percent_off"] = $off;
        } else {
            $options["amount_off"] = intval($off);
        }

        $options = array_merge($options, [
            "name"     => $name,
            "duration" => strtolower($duration),
            "currency" => $this->preferredCurrency(),
        ]);

        /** @var StipeCoupon */
        $objCoupon = StipeCoupon::create($options, $this->stripeOptions());

        return ($objCoupon);
    }

    /**
     * @param string $description An arbitary string which you can attach to the invoice item. The description is displayed in the invoice for easy tracking.
     * @param int $unitAmount The integer unit amount in paise of the charge to be applied to the upcoming invoice. This $unitAmount will be multiplied by the quantity to get the full amount.
     * @param int $quantity Non-negative integer. The quantity of units for the invoice item.
     * @param int $discount Non-negative integer. The discount to apply to this invoice item.
     * @param int $totalDiscount
     * @param array $options Array containing the necessary params
     *      $options = [
     *          'currency'          => (string) Three-letter ISO currency code, in lowercase Optional.
     *          'tax_rates'         => The tax rates which apply to the invoice item. When set, the default_tax_rates on the invoice do not apply to this invoice item Optional.
     *      ]
     *
     * @return StripeInvoiceItem
     * @throws \Laravel\Cashier\Exceptions\InvalidStripeCustomer
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createInvoiceItem(string $description, int $unitAmount, int $quantity, int $discount, int $totalDiscount = 0, array $options = []): StripeInvoiceItem {
        $this->assertCustomerExists();

        $options = array_merge([
            "customer"     => $this->stripe_id,
            "discountable" => true,
            "unit_amount"  => ceil($unitAmount * (1 - $discount / 100) * (1 - $totalDiscount / 100)),
            "currency"     => $this->preferredCurrency(),
            "description"  => $description,
            "quantity"     => $quantity,
        ], $options);

        return (StripeInvoiceItem::create($options, $this->stripeOptions()));
    }
}

<?php

namespace App\Repositories\Accounting;

use Carbon\Carbon;
use App\Helpers\Util;
use Laravel\Cashier\PaymentMethod;
use App\Repositories\BaseRepository;
use App\Models\{Accounting\AccountingFailedPayments, Soundblock\Service};

class AccountingFailedPaymentsRepository extends BaseRepository {
    /**
     * @var AccountingFailedPayments
     */
    private AccountingFailedPayments $failedPayments;

    /**
     * FinanceFailedPaymentsRepository constructor.
     * @param AccountingFailedPayments $failedPayments
     */
    public function __construct(AccountingFailedPayments $failedPayments) {
        $this->failedPayments = $failedPayments;
    }

    /**
     * @param Service $service
     * @param PaymentMethod $paymentMethod
     * @param float $amount
     * @param string $failReason
     * @throws \Exception
     */
    public function logFailedPayment(Service $service, PaymentMethod $paymentMethod, float $amount, string $failReason) {
        $service->failedPayments()->create([
            "row_uuid"                     => Util::uuid(),
            "service_uuid"                 => $service->service_uuid,
            "fail_reason"                  => $failReason,
            "failed_amount"                => $amount,
            "failed_date"                  => Carbon::now(),
            "failed_stripe_payment_id"     => $paymentMethod->id,
            "failed_stripe_card_brand"     => $paymentMethod->card->brand,
            "failed_stripe_card_last_four" => $paymentMethod->card->last4,
        ]);
    }
}

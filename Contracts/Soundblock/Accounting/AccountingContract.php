<?php

namespace App\Contracts\Soundblock\Accounting;

use App\Models\User;
use Laravel\Cashier\PaymentMethod;
use App\Models\Soundblock\Service;

interface AccountingContract {
    public function makeCharge(Service $service, float $planCost, ?PaymentMethod $paymentMethod = null): bool;

    public function setPaymentStatus(Service $service, string $status): void;

    public function chargeUserImmediately(User $user, PaymentMethod $paymentMethod): void;
}

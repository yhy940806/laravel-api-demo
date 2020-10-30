<?php

namespace App\Contracts\Payment;

use Laravel\Cashier\PaymentMethod;
use Stripe\Customer;
use App\Models\User;

interface PaymentContract {
    public function getOrCreateCustomer(User $user): Customer;

    public function getUserPaymentMethods(User $user): array;

    public function addPaymentMethod(User $user, string $paymentMethodId): PaymentMethod;

    public function deletePaymentMethod(User $user, string $paymentMethodId): void;

    public function deleteUserPaymentMethods(User $user): void;

    public function updateDefaultMethod(User $user, string $methodId): void;
}

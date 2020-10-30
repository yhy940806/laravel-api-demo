<?php

namespace App\Services\Payment;

use Stripe\Customer;
use App\Models\User;
use Laravel\Cashier\PaymentMethod;
use App\Contracts\Payment\PaymentContract;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PaymentService implements PaymentContract {

    /**
     * @param User $user
     * @param string $paymentMethodId
     * @return PaymentMethod
     */
    public function addPaymentMethod(User $user, string $paymentMethodId): PaymentMethod {
        if (is_null($user->defaultPaymentMethod())) {
            return $user->updateDefaultPaymentMethod($paymentMethodId);
        }

        return $user->addPaymentMethod($paymentMethodId);
    }

    /**
     * @param User $user
     * @return Customer
     */
    public function getOrCreateCustomer(User $user): Customer {
        $userEmail = $user->emails->first();

        if (is_null($userEmail)) {
            throw new AccessDeniedHttpException("You must to have email address.");
        }

        return $user->createOrGetStripeCustomer([
            "email"    => $userEmail->user_auth_email,
            "name"     => $user->full_name,
            "metadata" => [
                "user_uuid" => $user->user_uuid,
            ],
        ]);
    }

    /**
     * @param User $user
     * @return array
     */
    public function getUserPaymentMethods(User $user): array {
        $methods = [];

        $userMethodsCollection = $user->paymentMethods();

        /** @var PaymentMethod $paymentMethod */
        foreach ($userMethodsCollection as $paymentMethod) {
            $methods[] = $paymentMethod->asStripePaymentMethod()->toArray();
        }

        return $methods;
    }

    /**
     * @param User $user
     * @param string $paymentMethodId
     */
    public function deletePaymentMethod(User $user, string $paymentMethodId): void {
        $paymentMethod = $user->findPaymentMethod($paymentMethodId);
        $paymentMethod->delete();
    }

    /**
     * @param User $user
     */
    public function deleteUserPaymentMethods(User $user): void {
        $user->deletePaymentMethods();
    }

    /**
     * @param User $user
     * @param string $methodId
     */
    public function updateDefaultMethod(User $user, string $methodId): void {
        $user->updateDefaultPaymentMethod($methodId);
    }
}

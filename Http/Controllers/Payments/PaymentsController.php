<?php

namespace App\Http\Controllers\Payments;

use App\Models\User;
use App\Facades\Exceptions\Disaster;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Exceptions\PaymentTaskException;
use App\Contracts\Payment\PaymentContract;
use App\Http\Requests\Payments\AddPaymentMethodRequest;
use App\Contracts\Soundblock\Accounting\AccountingContract;
use Stripe\Exception\{CardException, InvalidRequestException};

class PaymentsController extends Controller {
    /**
     * @group Payments
     * @bodyParam payment_id string required The id of stripe payment method. Example: pm_****
     * @authenticated
     * @response 201 ''
     *
     * @param AddPaymentMethodRequest $request
     * @param PaymentContract $payment
     * @param AccountingContract $accountingContract
     * @return \Illuminate\Http\JsonResponse
     */
    public function addPaymentMethod(AddPaymentMethodRequest $request, PaymentContract $payment, AccountingContract $accountingContract) {
        /** @var User $user */
        $user = Auth::user();
        $payment->getOrCreateCustomer($user);

        try {
            $paymentMethod = $payment->addPaymentMethod($user, $request->input("payment_id"));
            $accountingContract->chargeUserImmediately($user, $paymentMethod);
        } catch (CardException $exception) {
            abort(400, $exception->getMessage());
        } catch (InvalidRequestException $requestException) {
            abort(400, $requestException->getMessage());
        } catch (PaymentTaskException $exception) {
            Disaster::handleDisaster($exception);

            abort(400, "Something went wrong");
        } catch (\Exception $e) {
            abort(400, "Something went wrong");
        }

        return response()->json('', 201);
    }

    /**
     * @group Payments
     * @authenticated
     * @responseFile responses/payments/methods/methods.get.json
     *
     * @param PaymentContract $payment
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentMethods(PaymentContract $payment) {
        /** @var User $user */
        $user = Auth::user();

        try {
            $stripeCustomer = $user->asStripeCustomer();
        } catch (\Exception $e) {
            abort(400, 'User is not a stripe customer');
        }

        return response()->json($payment->getUserPaymentMethods($user));
    }

    /**
     * @group Payments
     * @urlParam methodId The id of stripe payment method. Example: pm_****. If not provided remove all customer payment methods
     * @authenticated
     * @response 204 ''
     *
     * @param PaymentContract $payment
     * @param string|null $methodId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePaymentMethod(PaymentContract $payment, ?string $methodId = null) {
        /** @var User $user */
        $user = Auth::user();

        try {
            $stripeCustomer = $user->asStripeCustomer();
        } catch (\Exception $e) {
            abort(400, 'User is not a stripe customer');
        }

        if (is_null($methodId)) {
            $payment->deleteUserPaymentMethods($user);
        } else {
            $paymentMethod = $user->findPaymentMethod($methodId);

            if (is_null($paymentMethod)) {
                abort(400, 'Payment ID is not valid');
            }

            $payment->deletePaymentMethod($user, $methodId);
        }

        return response()->json('', 204);
    }

    /**
     * @group Payments
     * @urlParam methodId required The id of stripe payment method. Example: pm_****.
     * @authenticated
     * @response 204 ''
     *
     * @param PaymentContract $payment
     * @param string $methodId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDefaultPayment(PaymentContract $payment, string $methodId) {
        /** @var User $user */
        $user = Auth::user();

        try {
            $stripeCustomer = $user->asStripeCustomer();
        } catch (\Exception $e) {
            abort(400, 'User is not a stripe customer');
        }

        $paymentMethod = $user->findPaymentMethod($methodId);

        if (is_null($paymentMethod)) {
            abort(400, 'Payment ID is not valid');
        }

        $payment->updateDefaultMethod($user, $methodId);

        return response()->json('', 204);
    }
}

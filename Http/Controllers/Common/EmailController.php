<?php

namespace App\Http\Controllers\Common;

use Mail;
use Auth;
use Client;
use App\Models\{Core\App, User};
use App\Mail\Apparel\TourMaskMail;
use App\Facades\Accounting\Invoice;
use App\Http\Controllers\Controller;
use App\Contracts\Accounting\InvoiceContract;
use App\Http\Transformers\User\EmailTransformer;
use App\Services\{EmailService, User\UserCorrespondenceService, UserService};

class EmailController extends Controller {
    public function send(UserService $userService, UserCorrespondenceService $correspondenceService) {
        try {
            foreach (User::find([3, 4]) as $user) {
                Mail::to($user->recpient())->send(new TourMaskMail(App::find(4)));
            }

            return ($this->apiReply());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @group Email Verification
     * @urlParam required hash
     * @param string $hash
     * @param EmailService $emailService
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function verifyEmail(string $hash, EmailService $emailService) {
        try {
            $objEmail = $emailService->verifyEmailByHash($hash);

            return ($this->response->item($objEmail, new EmailTransformer));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @group Email Verification
     * @authenticated
     * @urlParam email required Email UUID
     *
     * @param string $email
     * @param EmailService $emailService
     * @return Response
     */
    public function sendVerifyEmailMessage(string $email, EmailService $emailService) {
        try {
            /** @var User */
            $objUser = Auth::user();
            $objEmail = $emailService->findForUser($objUser, $email);

            if (is_null($objEmail)) {
                abort(404, "Email Not Found.");
            }

            $objEmail = $emailService->sendVerificationEmail($objEmail);

            return ($this->response->item($objEmail, new EmailTransformer));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function testInvoice(InvoiceContract $invoiceContract) {
        try {
            $objInvoice = $invoiceContract->createInvoiceFor(Auth::user(), Client::app(), "Test Invoice", 10000);
            return ($objInvoice->payment->first());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function testCreateCoupon() {
        try {
            return Invoice::createCoupon(Auth::user(), "Test Coupon", "repeating", 250, false, ["duration_in_months" => 3]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function testCreateInvoiceItem() {
        try {
            return Invoice::createInvoiceItem(Auth::user(), "Merch Product", 100, 5, 6);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function testCreateInvoice() {
        try {
            Invoice::createInvoiceItem(Auth::user(), "Merch Product", 100, 5, 6);
            return Invoice::createInvoice(Auth::user(), Client::app(), ["coupon" => "iv9F9Lly"]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

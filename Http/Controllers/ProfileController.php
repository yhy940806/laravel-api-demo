<?php

namespace App\Http\Controllers;

use Util;
use Auth;
use Exception;
use App\Models\User;
use Dingo\Api\Http\Response;
use App\Services\{AuthService,
    EmailService,
    PaymentService,
    PhoneService,
    PostalService,
    UserService
};
use App\Http\Resources\Common\BaseCollection;
use App\Services\Core\Auth\AuthPermissionService;
use App\Http\Requests\User\Profile\{GetAddressRequest,
    GetBankAccountRequest,
    GetEmailsRequest,
    GetPaypalsRequest,
    GetPhonesRequest,
    UpdateBankAccountRequest,
    UpdateEmailRequest,
    UpdatePaypalRequest,
    UpdatePhoneRequest,
    UpdatePostalRequest
};
use App\Http\Requests\Soundblock\Profile\{AddBankAccountRequest,
    AddEmailRequest,
    AddPayPalRequest,
    AddPhoneRequest,
    AddPostalRequest,
    DeletePhoneRequest,
    DeleteBankAccountRequest,
    DeleteEmailRequest,
    DeletePayPalRequest,
    DeletePostalRequest,
    SetPrimaryRequest,
    UpdateNameRequest
};
use App\Http\Requests\Soundblock\Bootloader\User\GetUserRequest;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ProfileController extends Controller {
    /** @var AuthService $authService */
    protected AuthService $authService;
    /** @var AuthPermissionService $authPermService */
    protected AuthPermissionService $authPermService;

    /**
     * @param AuthService $authService
     * @param AuthPermissionService $authPermService
     * @return void
     */
    public function __construct(AuthService $authService, AuthPermissionService $authPermService) {
        $this->authService = $authService;
        $this->authPermService = $authPermService;
    }

    /**
     * @responseFile responses/soundblock/profile/get-profile.get.json
     *
     * @return Response
     * @throws Exception
     */
    public function index() {
        try {
            /** @var User */
            $user = Auth::user();

            return($user->load(["emails", "phones", "postals", "paypals", "bankings"]));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param GetUserRequest $objRequest
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function show(GetUserRequest $objRequest, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $user = $userService->find($objRequest->user);
            } else {
                /** @var \App\Models\User */
                $user = Auth::user();
            }
            $user->load(["emails", "aliases", "phones", "postals", "paypals", "bankings"])
                    ->setAppends(["name", "avatar"])->makeVisible(["name_first", "name_middle", "name_last"]);

            return($this->apiReply($user));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @queryParam user uuid optional User UUID
     * @queryParam per_page integer required Items per page
     *
     * @param GetPhonesRequest $objRequest
     * @param PhoneService $phoneService
     * @param UserService $userService
     * @return BaseCollection
     * @throws Exception
     */
    public function getPhones(GetPhonesRequest $objRequest, PhoneService $phoneService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user);
            } else {
                $objUser = Auth::user();
            }
            $arrPhones = $phoneService->findByUser($objUser, $objRequest->per_page);

            return(new BaseCollection($arrPhones));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/soundblock/profile/add-phone.post.json
     * @responseFile 417 responses/soundblock/profile/add-phone_error.post.json
     * @bodyParam phone_type string required phone_type
     * @bodyParam phone_number string required The phone number
     * @bodyParam flag_primary bool optional Primary or not
     * @bodyParam user uuid optional User UUID
     *
     * @param AddPhoneRequest $objRequest
     * @param UserService $userService
     * @param PhoneService $phoneService
     * @return Response
     * @throws Exception
     */

    public function storePhone(AddPhoneRequest $objRequest, UserService $userService, PhoneService $phoneService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user, true);
            } else {
                $objUser = Auth::user();
            }
            $phone = $phoneService->create($objRequest->all(), $objUser);

            return($this->apiReply($phone, "Added new phone", 201));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @bodyParam user uuid optional User UUID
     * @bodyParam old_phone_number string required
     * @bodyParam flag_primary bool optional Is primary or not
     *
     * @param UpdatePhoneRequest $objRequest
     * @param PhoneService $phoneService
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function updatePhone(UpdatePhoneRequest $objRequest, PhoneService $phoneService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $user = $userService->find($objRequest->user);
            } else {
                /** @var User */
                $user = Auth::user();
            }
            $phone = $phoneService->find($objRequest->old_phone_number, $user);

            if (!$phone)
                throw new BadRequestHttpException("user has n't this phone");
            $phone = $phoneService->update($phone, $user, $objRequest->all());

            return($this->apiReply($phone));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/soundblock/profile/delete-phone.delete.json
     * @queryParam user uuid optional User UUID
     * @queryParam phone_number string required Phone number
     *
     * @param DeletePhoneRequest $objRequest
     * @param PhoneService $phoneService
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function deletePhone(DeletePhoneRequest $objRequest, PhoneService $phoneService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user);
            } else {
                $objUser = Auth::user();
            }

            $phoneService->delete($objRequest->phone_number, $objUser);

            return ($this->apiReply(null, "Deleted phone"));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @queryParam user uuid optional User UUID
     * @queryParam per_page integer required Items per page
     *
     * @param GetAddressRequest $objRequest
     * @param PostalService $postalService
     * @param UserService $userService
     * @return BaseCollection
     * @throws Exception
     */
    public function getPostals(GetAddressRequest $objRequest, PostalService $postalService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user, true);
            } else {
                $objUser = Auth::user();
            }

            $arrPostals = $postalService->findByUser($objUser, $objRequest->per_page);
            return(new BaseCollection($arrPostals));
        } catch (Exception $e) {
            throw $e;
        }

    }

    /**
     * @bodyParam postal_type string required postal_type
     * @bodyParam postal_street string required The postal street
     * @bodyParam postal_city string required
     * @bodyParam postal_zipcode string required The postal zip code
     * @bodyParam postal_country string required The postal country
     * @bodyParam user uuid optional User UUID
     *
     * @param AddPostalRequest $objRequest
     * @param PostalService $postalService
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function storePostal(AddPostalRequest $objRequest, PostalService $postalService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user, true);
            } else {
                $objUser = Auth::user();
            }
            $objPostal = $postalService->create($objRequest->all(), $objUser);

            return($this->apiReply($objPostal));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @bodyParam postal required Postal UUID
     * @bodyParam postal_type string optional The postal type
     * @bodyParam postal_street string optional
     * @bodyParam postal_city string optional
     * @bodyParam postal_zipcode string optional
     * @bodyParam postal_country string optional
     * @bodyParam flag_primary bool optional
     * @bodyParam user uuid optional User UUID
     *
     * @param UpdatePostalRequest $objRequest
     * @param PostalService $postalService
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function updatePostal(UpdatePostalRequest $objRequest, PostalService $postalService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user, true);
            } else {
                $objUser = Auth::user();
            }
            $objPostal = $postalService->find($objRequest->postal, true);
            $objPostal = $postalService->update($objPostal, $objUser, $objRequest->all());

            return($this->apiReply($objPostal));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/soundblock/profile/delete-postal.delete.json
     * @queryParam postal uuid required Postal UUID
     * @queryParam user uuid optional User UUID
     *
     * @param DeletePostalRequest $objRequest
     * @param PostalService $postalService
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function deletePostal(DeletePostalRequest $objRequest, PostalService $postalService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user, true);
            } else {
                $objUser = Auth::user();
            }

            if ($postalService->delete($objRequest->postal, $objUser)) {
                return ($this->apiReply());
            } else {
                return ($this->apiReject());
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @queryParam per_page integer required Items per page
     * @queryParam user uuid optional User UUID required if the app is office
     *
     * @param GetEmailsRequest $objRequest
     * @param EmailService $emailService
     * @param UserService $userService
     * @return BaseCollection
     * @throws Exception
     */
    public function getEmails(GetEmailsRequest $objRequest, EmailService $emailService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user, true);
            } else {
                $objUser = Auth::user();
            }
            $arrEmails = $emailService->findByUser($objUser, $objRequest->per_page);

            return(new BaseCollection($arrEmails));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/soundblock/profile/add-email.post.json
     * @responseFile 417 responses/soundblock/profile/add-email_error.post.json
     * @bodyParam user_auth_email email required The user email
     * @bodyParam user uuid optional User UUID
     *
     * @param AddEmailRequest $objRequest
     * @param EmailService $emailService
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function storeEmail(AddEmailRequest $objRequest, EmailService $emailService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user, true);
            } else {
                $objUser = Auth::user();
            }
            $objEmail = $emailService->create($objRequest->input("user_auth_email"), $objUser);
            $objEmail = $emailService->sendVerificationEmail($objEmail);

            return($this->apiReply($objEmail));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @bodyParam old_user_auth_email email required The old email
     * @bodyParam user_auth_email email required The email
     * @bodyParam flag_primary bool optional
     * @bodyParam user uuid optional User UUID
     *
     * @param UpdateEmailRequest $objRequest
     * @param EmailService $emailService
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function updateEmail(UpdateEmailRequest $objRequest, EmailService $emailService, UserService $userService) {
        try {
            $objEmail = $emailService->find($objRequest->old_user_auth_email, true);
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user, true);
            } else {
                $objUser = Auth::user();
            }
            $objEmail = $emailService->update($objEmail, $objUser, $objRequest->all());

            return($this->apiReply($objEmail));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/soundblock/profile/delete-email.delete.json
     * @queryParam user_auth_email email required
     * @queryParam user uuid optional User UUID
     *
     * @param DeleteEmailRequest $objRequest
     * @param EmailService $emailService
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function deleteEmail(DeleteEmailRequest $objRequest, EmailService $emailService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user, true);

            } else {
                $objUser = Auth::user();
            }
            $objEmail = $emailService->find($objRequest->user_auth_email);

            if (!$emailService->userHasEmail($objUser, $objEmail))
                throw abort(400, "The user has n't this email.");
            if ($emailService->delete($objRequest->user_auth_email, $objUser)) {
                return ($this->apiReply());
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @bodyParam user uuid optional User UUID
     * @bodyParam name_first string required
     * @bodyParam name_middle string required
     * @bodyParam name_last string required
     *
     * @param UpdateNameRequest $objRequest
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function updateName(UpdateNameRequest $objRequest, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user, true);
            } else {
                $objUser = Auth::user();
            }
            $arrName = Util::parse_name($objRequest->name);
            $objUser = $userService->update($objUser, $arrName);

            return($this->apiReply($objUser));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @queryParam user uuid optional User UUID
     *
     * @param GetBankAccountRequest $objRequest
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function getBankings(GetBankAccountRequest $objRequest, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user);
            } else {
                $objUser = Auth::user();
            }
            $arrBankAccs = $objUser->bankings;

            return($this->apiReply($arrBankAccs));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/soundblock/profile/add-bankaccount.post.json
     * @bodyParam bank_name string required The bank name
     * @bodyParam account_type string required The account type
     * @bodyParam account_number digits required The account number(1 to 25 digits)
     * @bodyParam routing_number digits required The routing number(9 digits)
     * @bodyParam flag_primary bool optional
     * @bodyParam user uuid optional User UUID
     *
     * @param AddBankAccountRequest $objRequest
     * @param PaymentService $paymentService
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function storeBanking(AddBankAccountRequest $objRequest, PaymentService $paymentService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user, true);
            } else {
                $objUser = Auth::user();
            }
            $objBank = $paymentService->createBanking($objRequest->all(), $objUser);

            return($this->apiReply($objBank));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @bodyParam bank uuid required Bank UUID
     * @bodyParam bank_name string optional The bank name
     * @bodyParam account_type string optional The account type
     * @bodyParam account_number digits optional The account number(1 to 25 digits)
     * @bodyParam routing_number digits optional The routing number(9 digits)
     * @bodyParam flag_primary bool optional
     * @bodyParam user uuid optional User UUID
     *
     * @param UpdateBankAccountRequest $objRequest
     * @param PaymentService $paymentService
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function updateBanking(UpdateBankAccountRequest $objRequest, PaymentService $paymentService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user, true);
            } else {
                $objUser = Auth::user();
            }
            $objBanking = $paymentService->findBanking($objRequest->bank, true);
            $objBanking = $paymentService->updateBanking($objBanking, $objUser, $objRequest->all());

            return($this->apiReply($objBanking));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/soundblock/profile/set-primary.patch.json
     * @bodyParam type string required value in bank or paypal
     * @bodyParam bank uuid required if type is bank Bank UUID
     * @bodyParam paypal uuid required if type is paypal Paypal UUID
     * @bodyParam flag_primary bool required
     * @bodyParam user uuid optional User UUID required if the app is office
     *
     * @param SetPrimaryRequest $objRequest
     * @param PaymentService $paymentService
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function setPrimary(SetPrimaryRequest $objRequest, PaymentService $paymentService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user, true);
            } else {
                $objUser = Auth::user();
            }
            $object = $paymentService->setPrimary($objRequest->all(), $objUser);

            return($this->apiReply($object));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/soundblock/profile/delete-bankaccount.delete.json
     * @queryParam bank uuid required Bank UUID
     * @qeuryParam user uuid optional User UUID
     *
     * @param DeleteBankAccountRequest $objRequest
     * @param PaymentService $paymentService
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function deleteBankAccount(DeleteBankAccountRequest $objRequest, PaymentService $paymentService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user, true);
            } else {
                $objUser = Auth::user();
            }
            $paymentService->deleteBanking($objRequest->bank, $objUser);

            return ($this->apiReply(null, "Deleted bank account"));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @queryParam user uuid optional User UUID required if the app is office
     *
     * @param GetPaypalsRequest $objRequest
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function getPaypals(GetPaypalsRequest $objRequest, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $user = $userService->find($objRequest->user, true);
            } else {
                /** @var User */
                $user = Auth::user();
            }

            return($this->apiReply($user->paypals()->orderBy("flag_primary", "desc")->get()));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/soundblock/profile/add-paypal.post.json
     * @bodyParam user uuid optional User UUID
     * @bodyParam paypal_email email required The paypal email
     * @bodyParam flag_primary bool optional
     *
     * @param AddPayPalRequest $objRequest
     * @param PaymentService $paymentService
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function storePaypal(AddPayPalRequest $objRequest, PaymentService $paymentService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $user = $userService->find($objRequest->user, true);
            } else {
                /** @var User */
                $user = Auth::user();
            }
            $paypal = $paymentService->createPaypal($objRequest->all(), $user);

            return($this->apiReply($paypal));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @bodyParam paypal uuid required Paypal UUID
     * @bodyParam paypal_email email required The paypal email
     * @bodyParam flag_primary bool optional
     * @bodyParam user uuid optional User UUID required if the app is office
     *
     * @param UpdatePaypalRequest $objRequest
     * @param PaymentService $paymentService
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function updatePaypal(UpdatePaypalRequest $objRequest, PaymentService $paymentService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user, true);
            } else {
                $objUser = Auth::user();
            }
            $objPayapal = $paymentService->findPaypal($objRequest->paypal, true);
            $objPayapal = $paymentService->updatePaypal($objPayapal, $objUser, $objRequest->all());

            return($this->apiReply($objPayapal));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/soundblock/profile/delete-paypal.delete.json
     * @queryParam paypal uuid required Paypal UUID
     * @queryParam user uuid optional User UUID
     *
     * @param DeletePayPalRequest $objRequest
     * @param PaymentService $paymentService
     * @param UserService $userService
     * @return Response
     * @throws Exception
     */
    public function deletePaypal(DeletePayPalRequest $objRequest, PaymentService $paymentService, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $userService->find($objRequest->user, true);
            } else {
                $objUser = Auth::user();
            }
            $paymentService->deletePaypal($objRequest->paypal, $objUser);

            return ($this->apiReply(null, "Deleted paypal"));
        } catch (Exception $e) {
            throw $e;
        }
    }

}

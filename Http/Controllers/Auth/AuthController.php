<?php

namespace App\Http\Controllers\Auth;

use Auth;
use Route;
use Exception;
use App\Models\User;
use Dingo\Api\Http\Response;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\{
    SignUpRequest,
    SigninRequest,
    RefreshTokenRequest,
    ResetPasswordRequest,
    CheckPasswordRequest,
    ForgotPasswordRequest,
    UpdatePasswordRequest};
use App\Http\Controllers\Controller;
use App\Services\{AuthService, AliasService, EmailService, PhoneService, UserService};

/**
 * @group Authentication
 *
 * APIs for authentication
 */
class AuthController extends Controller {
    /**
     * @var AuthService
     */
    protected $authService;
    /**
     * @var UserService
     */
    protected $userService;
    /**
     * @var AliasService
     */
    protected $aliasService;
    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * @param AuthService $authService
     * @param UserService $userService
     * @param AliasService $aliasService
     * @param EmailService $emailService
     * @param PhoneService $phoneService
     */
    public function __construct(AuthService $authService, UserService $userService, AliasService $aliasService,
                                EmailService $emailService, PhoneService $phoneService) {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->aliasService = $aliasService;
        $this->emailService = $emailService;
        $this->phoneService = $phoneService;
    }

    /**
     * @responseFile responses/auth/signin.post.json
     * @responseFile 417 responses/auth/signin_error.post.json
     * @param SigninRequest $objRequest
     * @return
     */
    public function signIn(SigninRequest $objRequest) {
        request()->request->add([
            "grant_type"    => "password",
            "client_id"     => env("PASSWORD_CLIENT_ID"),
            "client_secret" => env("PASSWORD_CLIENT_SECRET"),
            "username"      => $objRequest->get("user"),
            "password"      => $objRequest->get("password"),
        ]);

        $response = Route::dispatch(Request::create("/oauth/token", "POST"));

        $data = json_decode($response->getContent(), true);

        if (!$response->isOk()) {
            return($this->apiReject($data["response"], $data["status"]["message"], $response->getStatusCode()));
        }

        $objUser = $this->userService->findByEmailOrAlias($objRequest->get("user"));

        return ($this->apiReply([
            "auth" => $data,
            "user" => $objUser->user_uuid,
        ]));
    }

    /**
     * @return Response
     */
    public function signOut() {
        /** @var User $user */
        $user = Auth::user();
        $user->token()->revoke();

        return ($this->apiReply());
    }

    /**
     * @bodyParam name_first string required User First Name
     * @bodyParam email string required User Email
     * @bodyParam user_password string required User Password
     * @bodyParam user_password_confirmation string required User Password Confirmation
     *
     * @responseFile responses/auth/signin.post.json
     * @param SignUpRequest $objRequest
     * @return
     * @throws Exception
     */
    public function signUp(SignUpRequest $objRequest) {
        $objUser = $this->userService->create($objRequest->only("name_first", "user_password"));
        $objEmail = $this->emailService->create($objRequest->input("email"), $objUser);
        $this->emailService->sendVerificationEmail($objEmail);

        return ($this->apiReply([
            "user" => $objUser->user_uuid,
        ], "Please verified your email."));
    }


    public function userData(Request $objRequest) {
        /** @var User */
        $user = Auth::user();

        return($this->apiReply($user->load(["aliases", "emails"])->append(["avatar"])));
    }

    public function userRefresh(RefreshTokenRequest $objRequest) {
        // if (!$objRequest->headers->has("Authorization")) {
        //     throw new UnauthorizedException();
        // }

        // Tokenize from the header.
        // $refreshToken = trim(preg_replace("/^(?:\s+)?Bearer\s/", "", $objRequest->header("Authorization")));

        request()->request->add([
            "grant_type"    => "refresh_token",
            "refresh_token" => $objRequest->refresh_token,
            "client_id"     => env("PASSWORD_CLIENT_ID"),
            "client_secret" => env("PASSWORD_CLIENT_SECRET"),
        ]);
        $response = Route::dispatch(Request::create("/oauth/token", "POST"));

        $data = json_decode($response->getContent(), true);

        if (!$response->isOk()) {
            return ($this->apiReject($data["response"], $data["status"]["message"], $response->getStatusCode()));
        }

        return ($this->apiReply($data));
    }

    public function checkPassword(CheckPasswordRequest $objRequest) {
        try {
            return ($this->apiReply([
                "data" => $this->authService->checkPassword($objRequest->current_password),
            ]));
        } catch (Exception $e) {
            throw $e;
        }
    }


    /**
     * @group Authentication
     * @bodyParam Email email optional User Email
     * @bodyParam Alias string optional User Alias
     * @bodyParam Phone string optional User Contact Phone
     * @param ForgotPasswordRequest $request
     * @return mixed
     * @throws Exception
     */
    public function sendPasswordResetMail(ForgotPasswordRequest $request) {
        try {
            if (empty($request->all())){
                abort(404, "No data provided");
            }

            if($request->has("email")){
                $objEmail = $this->emailService->find($request->email, true);

                if(is_null($objEmail)){
                    abort(404, "User not found");
                }

                $user     = $objEmail->user;
            } else if ($request->has("alias")){
                $objAlias = $this->aliasService->find($request->alias);

                if(is_null($objAlias)){
                    abort(404, "User not found");
                } else if (!$objAlias->flag_primary){
                    abort(404, "Alias is not primary");
                }

                $user     = $objAlias->user;
                $objEmail = $user->emails()->where("flag_primary", true)->first();
            } else if ($request->has("phone")){
                $objPhone = $this->phoneService->findByPhone($request->phone);

                if(is_null($objPhone)){
                    abort(404, "User not found");
                }

                $user     = $objPhone->user;
                $objEmail = $user->emails()->where("flag_primary", true)->first();
            }

            if (!$objEmail->flag_verified) {
                abort(400, "This email is not verified yet.");
            }

            $this->authService->prepareForPasswordReset($user);

            return $this->apiReply();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function passwordReset(string $resetToken, ResetPasswordRequest $request, AuthService $authService) {
        try {
            $passwordReset = $authService->validateResetToken($resetToken);
            if (!$passwordReset) {
                abort(400, "This token is expired or invalid.");
            }
            $user = $authService->passwordReset($passwordReset, $request->new_password);

            return ($this->apiReply($user, "Reset your password", 202));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function changePassword(UpdatePasswordRequest $request, AuthService $authService) {
        try {
            if (!$authService->checkPassword($request->current_password)) {
                abort(400, "Invalid password");
            }
            $user = $authService->changePassword($request->new_password);

            return ($this->apiReply($user, "Changed your password", 202));
        } catch (Exception $e) {
            throw $e;
        }
    }
}

<?php

namespace App\Http\Controllers\Auth;

use Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\{AuthService, EmailService};
use PragmaRX\Google2FAQRCode\Google2FA;

class LoginSecurityController extends Controller {
    /** @var Google2FA */
    protected Google2FA $google2fa;

    public function __construct() {
        $this->google2fa = new Google2FA();
    }

    public function show2faForm(Request $request, EmailService $emailService) {
        /** @var User $user */
        $user = Auth::user();
        $google2fa_url = "";
        $secretKey = "";
        if ($user->emails->isEmpty())
            abort(422, "The user has not any email.");
        if ($user->loginSecurity()->exists()) {
            $google2fa_url = $this->google2fa->getQRCodeInline(
                "Arena Note Demo",
                $emailService->primary($user)->user_email,
                $user->loginSecurity->google2fa_secret
            );

            $secretKey = $user->loginSecurity->google2fa_secret;
        }

        $data = [
            "user"          => $user,
            "secret"        => $secretKey,
            "google2fa_url" => $google2fa_url,
        ];

        return (view("auth.2fa_setting")->with("data", $data));
    }

    public function generate2faSecret(AuthService $authService) {
        /** @var User */
        $user = Auth::user();
        $authService->generate2faSecret($user);

        return (redirect("/2fa")->with("success", "Secret key is generated."));
    }

    public function enable2fa(Request $request, AuthService $authService) {
        /** @var User $user */
        $user = Auth::user();
        $secret = $request->input("secret");
        $valid = $authService->verifyKey($user, $secret);
        if ($valid) {
            $authService->enableGoogle2FA($user);
            return (redirect("/2fa")->with("success", "2FA is enabled successfully."));
        } else {
            $authService->enableGoogle2FA($user);
            return (redirect("/2fa")->with("error", "Invalid verification Code, Please try again."));
        }
    }

    public function disable2fa(Request $request, AuthService $authService) {
        if (!$authService->checkPassword($request->current_password))
            return (redirect()->back()
                              ->with("error", "Your password does not match with your account password. Please try again."));

        $authService->disableGoogle2FA(Auth::user());
        return (redirect("/2fa")->with("success", "2FA is now disabled."));
    }
}

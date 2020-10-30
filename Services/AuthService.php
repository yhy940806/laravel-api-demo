<?php

namespace App\Services;

use Str;
use Hash;
use Mail;
use Util;
use Client;
use Exception;
use App\Mail\Auth\PasswordResetMail;
use App\Models\{
    Core\Auth\AuthGroup,
    Core\Auth\AuthPermission,
    User,
    Auth\LoginSecurity,
    PasswordReset
};
use Illuminate\Support\Facades\Auth;
use App\Repositories\{Core\Auth\AuthGroupRepository,
    Core\Auth\AuthPermissionRepository,
    Auth\LoginSecurityRepository,
    Auth\PasswordResetRepository,
    Common\AppRepository,
    User\UserRepository
};
use PragmaRX\Google2FAQRCode\Google2FA;
use App\Jobs\Auth\DeletePasswordResetJob;

class AuthService {
    const RESET_TOKEN_EXPIRED_TIME = 15;
    /** @var AppRepository */
    protected AppRepository $appRepo;
    /** @var AuthGroupRepository */
    protected AuthGroupRepository $groupRepo;
    /** @var AuthPermissionRepository */
    protected AuthPermissionRepository $permRepo;
    /** @var LoginSecurityRepository */
    protected LoginSecurityRepository $loginSecurityRepo;
    /** @var Google2FA */
    protected Google2FA $google2fa;
    /**
     * @var PasswordResetRepository
     */
    private PasswordResetRepository $passwordResetRepo;
    /**
     * @var UserRepository $userRepository
     */
    private UserRepository $userRepository;

    /**
     * @param AppRepository $appRepo
     * @param AuthGroupRepository $groupRepo
     * @param AuthPermissionRepository $permRepo
     * @param PasswordResetRepository $passwordResetRepo
     * @param UserRepository $userRepository
     *
     * @return void
     */
    public function __construct(AppRepository $appRepo, AuthGroupRepository $groupRepo, AuthPermissionRepository $permRepo, LoginSecurityRepository $loginSecurityRepo, PasswordResetRepository $passwordResetRepo, UserRepository $userRepository) {
        $this->appRepo = $appRepo;
        $this->groupRepo = $groupRepo;
        $this->permRepo = $permRepo;
        $this->loginSecurityRepo = $loginSecurityRepo;
        $this->passwordResetRepo = $passwordResetRepo;
        $this->userRepository = $userRepository;
        // $this->google2fa = new Google2FA();
    }

    public function checkOfficeUser(): bool {
        /** @var array */
        $optionForOffice = [
            "group"      => "App.Office.Admin",
            "permission" => "App.Office.Admin.Default",
            "app"        => "office",
        ];

        return ($this->checkAuth($optionForOffice));
    }

    /**
     * @param array $arrAuth
     * @return bool
     * @throws Exception
     */
    public function checkAuth(array $arrAuth): bool {
        $reqProps = ["group", "permission", "app"];
        if (!Util::array_keys_exists($reqProps, $arrAuth))
            throw new Exception(sprintf("Must have the properties %s.", implode(",", $reqProps)));
        foreach ($arrAuth as $auth) {
            if (!is_string($auth))
                throw new Exception("Must be string");
        }
        $strGroupName = $arrAuth["group"];
        $strPermissionName = $arrAuth["permission"];
        $strAppName = $arrAuth["app"];

        return ($this->isAuthorized($strGroupName, $strPermissionName, false) && $this->checkApp($strAppName));
    }

    /**
     * Checking if user is authorized by permission for some action
     *
     * @param string $strGroup Group Name
     * @param string $strPermission Permission Name
     * @param bool $blFlagThrowable
     * @return bool
     * @throws Exception
     */
    public function isAuthorized(string $strGroup, string $strPermission, bool $blFlagThrowable = true) {
        try {
            /** @var User $objUser */
            $objUser = Auth::user();
            /** @var AuthGroup $objGroup */
            $objGroup = $objUser->groups()->where("group_name", $strGroup)->first();

            if (is_null($objGroup)) {
                throw new Exception("Group Not Found.");
            }

            /** @var AuthPermission $objPermission */
            $objPermission = $this->permRepo->findByName($strPermission, false);

            if (is_null($objPermission)) {
                throw new Exception("Permission Not Found.");
            }

            $checkPermission = $objUser->permissionsInGroup()->wherePivot("group_id", $objGroup->group_id)
                                       ->wherePivot("permission_id", $objPermission->permission_id)->first();

            if (isset($checkPermission)) {
                return ($checkPermission->pivot->permission_value);
            }

            $permissionInGroup = $objGroup->permissions()
                                          ->where("core_auth_permissions.permission_id", $objPermission->permission_id)
                                          ->first();

            if (is_null($permissionInGroup)) {
                throw new Exception("Permission Not Found In Group.");
            }

            return ($permissionInGroup->pivot->permission_value);
        } catch (\Exception $exception) {
            if ($blFlagThrowable) {
                throw $exception;
            }

            return false;
        }
    }

    /**
     * @param string $strAppName
     * @return bool
     */
    public function checkApp(string $strAppName): bool {
        return (Client::app()->app_name == strtolower($strAppName));
    }

    /**
     * @param string $curPassword
     * @param User $objUser
     * @return bool
     */
    public function checkPassword(string $curPassword, ?User $objUser = null): bool {
        if (is_null($objUser)) {
            $objUser = Auth::user();
        }
        return (Hash::check($curPassword, $objUser->user_password));
    }

    /**
     * @param User $user
     * @return LoginSecurity
     */
    public function generate2faSecret(User $user): LoginSecurity {
        $loginSecurity = $this->loginSecurityRepo->findOrNewByUser($user);
        return ($loginSecurity);
    }

    /**
     * @param User $user
     * @param string $secret
     * @return bool
     */
    public function verifyKey(User $user, string $secret): bool {
        if (!$user->loginSecurity)
            throw new Exception("Not Found Login Security");
        return ($this->google2fa->verifyKey($user->loginSecurity->google2fa_secret, $secret));
    }

    /**
     * @param User $user
     * @return LoginSecurity
     */
    public function enableGoogle2FA(User $user): LoginSecurity {
        if (!$user->loginSecurity)
            throw new Exception("Not Found Login Security", 422);

        return ($this->loginSecurityRepo->enableGoogle2FA($user->loginSecurity));
    }

    public function disableGoogle2FA(User $user): LoginSecurity {
        if (!$user->loginSecurity)
            throw new Exception("Not Found Login Security", 422);

        return ($this->loginSecurityRepo->disableGoogle2FA($user->loginSecurity));
    }

    /**
     * @param User $user
     *
     * @return void
     */
    public function prepareForPasswordReset(User $user) {
        $resetToken = Str::random(200);
        $passwordReset = $this->passwordResetRepo->create([
            "user_id"     => $user->user_id,
            "user_uuid"   => $user->user_uuid,
            "reset_token" => $resetToken,
        ]);
        Mail::to($user->recpient())->send(new PasswordResetMail(Client::app(), $resetToken));
        DeletePasswordResetJob::dispatch($passwordReset)
                              ->delay(now()->addMinutes(static::RESET_TOKEN_EXPIRED_TIME));
    }

    /**
     * @param string $resetToken
     *
     * @return PasswordReset|null
     */
    public function validateResetToken(string $resetToken) {
        $passwordReset = $this->passwordResetRepo->findByResetToken($resetToken);
        if (!$passwordReset || $passwordReset->flag_used || $passwordReset->{PasswordReset::STAMP_CREATED} + static::RESET_TOKEN_EXPIRED_TIME * 60 < time()) {
            return null;
        }

        return $passwordReset;
    }

    /**
     * @param PasswordReset $passwordReset
     *
     * @return \App\Models\User
     */
    public function passwordReset(PasswordReset $passwordReset, string $newPassword) {
        $this->passwordResetRepo->update($passwordReset, [
            "flag_used"                     => true,
            PasswordReset::EXPIRED_AT       => now(),
            PasswordReset::STAMP_EXPIRED    => time(),
            PasswordReset::STAMP_EXPIRED_BY => $passwordReset->user->user_id,
        ]);

        return $this->changePassword($newPassword, $passwordReset->user);
    }

    public function changePassword(string $newPassword, $user = null) {
        if (is_null($user)) {
            if (Auth::user()) {
                /** @var \App\Models\User */
                $user = Auth::user();
            } else {
                throw new Exception("Invalid Parameter.");
            }
        }

        return ($this->userRepository->update($user, [
            "user_password" => Hash::make($newPassword),
        ]));
    }
}

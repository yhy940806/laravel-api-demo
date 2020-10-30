<?php

namespace App\Repositories\Auth;

use Util;
use App\Repositories\BaseRepository;
use App\Models\{Auth\LoginSecurity, User};

class LoginSecurityRepository extends BaseRepository {
    protected $google2fa;

    /**
     * @param LoginSecurity $loginSecurity
     * @return void
     */
    public function __construct(LoginSecurity $loginSecurity) {
        $this->model = $loginSecurity;
        // $this->google2fa = new Google2FA();
    }

    /**
     * @param User $user
     * @param bool $bnFailure
     * @return LoginSecurity
     */
    public function findByUser(User $user, ?bool $bnFailure = false): LoginSecurity {
        $queryBuilder = $this->model->where("user_id", $user->user_id);
        if ($bnFailure) {
            return ($queryBuilder->firstOrFail());
        } else {
            return ($queryBuilder->first());
        }

    }

    /**
     * @param User $user
     * @return LoginSecurity
     */
    public function findOrNewByUser(User $user): LoginSecurity {
        $loginSecurity = $this->model->where("user_id", $user->user_id)->first();
        if (is_null($loginSecurity)) {
            $arrLoginSecurity = [
                "row_uuid"         => Util::uuid(),
                "user_id"          => $user->user_id,
                "user_uuid"        => $user->user_uuid,
                "google2fa_enable" => false,
                "google2fa_secret" => $this->google2fa->generateSecretKey(),
            ];
            $loginSecurity = $this->create($arrLoginSecurity);
        }

        return ($loginSecurity);
    }

    /**
     * @param LoginSecurity $loginSecurity
     * @return LoginSecurity
     */
    public function enableGoogle2FA(LoginSecurity $loginSecurity): LoginSecurity {
        return ($this->update($loginSecurity, ["google2fa_enable" => true]));
    }

    public function disableGoogle2FA(LoginSecurity $loginSecurity): LoginSecurity {
        return ($this->update($loginSecurity, ["google2fa_enable" => false]));
    }
}

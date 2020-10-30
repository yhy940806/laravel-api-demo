<?php

namespace App\Repositories\Auth;

use App\Models\PasswordReset;
use App\Repositories\BaseRepository;

class PasswordResetRepository extends BaseRepository {
    /**
     * @param PasswordReset $passwordRest
     *
     * @return void
     */
    public function __construct(PasswordReset $passwordRest) {
        $this->model = $passwordRest;
    }

    /**
     * @param $resetToken
     *
     * @return PasswordReset
     */
    public function findByResetToken(string $resetToken) {
        return $this->model->where("reset_token", $resetToken)->first();
    }
}

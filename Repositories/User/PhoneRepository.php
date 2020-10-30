<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Models\UserContactPhone;
use App\Repositories\BaseRepository;

class PhoneRepository extends BaseRepository {
    public function __construct(UserContactPhone $objPhone) {
        $this->model = $objPhone;
    }

    public function findByUser(string $phoneNumber, User $objUser, bool $bnFailure = false) {
        $queryBuilder = $this->model->where("phone_number", $phoneNumber)
                                    ->where("user_id", $objUser->user_id);

        if ($bnFailure) {
            return ($queryBuilder->firstOrFail());
        } else {
            return ($queryBuilder->first());
        }
    }

    /**
     * @param string $strPhoneNumber
     * @return mixed
     */
    public function findByPhone(string $strPhoneNumber) {
        $objPhoneNumber = $this->model->where("phone_number", $strPhoneNumber)->first();

        return ($objPhoneNumber);
    }
}

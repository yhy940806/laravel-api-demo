<?php

namespace App\Services;

use Auth;
use Util;
use App\Models\{User, UserContactPhone};
use App\Repositories\User\{PhoneRepository, UserRepository};
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PhoneService {
    /** @var UserRepository */
    protected UserRepository $userRepo;
    /** @var PhoneRepository */
    protected PhoneRepository $phoneRepo;

    /**
     * @param UserRepository $userRepo
     * @param PhoneRepository $phoneRepo
     * @return void
     */
    public function __construct(UserRepository $userRepo, PhoneRepository $phoneRepo) {
        $this->userRepo = $userRepo;
        $this->phoneRepo = $phoneRepo;
    }

    /**
     * @param User $objUser
     * @param int $perPage
     * @return
     */
    public function findByUser(?User $user = null, ?int $perPage = 5) {
        if (!$user) {
            /** @var User */
            $user = Auth::user();
        }

        return ($user->phones()->orderBy("flag_primary", "desc")->paginate($perPage));
    }

    /**
     * @param string $strPhoneNumber
     * @return mixed
     */
    public function findByPhone(string $strPhoneNumber) {
        $objPhoneNumber = $this->phoneRepo->findByPhone($strPhoneNumber);

        return ($objPhoneNumber);
    }

    /**
     * @param array $arrParams
     * @param User $objUser
     * @return UserContactPhone
     */
    public function create(array $arrParams, ?User $objUser = null): UserContactPhone {
        $arrPhone = [];
        if (is_null($objUser)) {
            $objUser = Auth::user();
        }

        $arrPhone["user_id"] = $objUser->user_id;
        $arrPhone["user_uuid"] = $objUser->user_uuid;
        $arrPhone["phone_type"] = Util::ucfLabel($arrParams["phone_type"]);
        $arrPhone["phone_number"] = $arrParams["phone_number"];

        if (isset($arrParams["flag_primary"]) && $arrParams["flag_primary"]) {
            $this->initForPrimary($objUser);
            $arrPhone["flag_primary"] = true;
        } else {
            $arrPhone["flag_primary"] = false;
        }

        return ($this->phoneRepo->create($arrPhone));
    }

    /**
     * @param User $objUser
     * @return void
     */
    public function initForPrimary(User $objUser) {
        $arrObjPhones = $objUser->phones;

        $arrObjPhones->transform(function ($objPhone, $key) {
            $objPhone->update(["flag_primary" => false]);
        });

    }

    /**
     * @param string $strPhone
     * @param User $objUser
     * @return bool
     *
     */
    public function delete(string $strPhone, User $objUser): bool {
        if (!$this->userHasPhone($objUser, $strPhone))
            throw new BadRequestHttpException("User has n't this phone");
        $objPhone = $this->find($strPhone, $objUser);

        return ($objPhone->delete());
    }

    /**
     * @param User $objUser
     * @param string $strPhoneNumber
     * @return bool
     */
    public function userHasPhone(User $objUser, string $strPhoneNumber): bool {
        return ($objUser->phones()->where("phone_number", $strPhoneNumber)->exists());
    }

    /**
     * @param string $phone
     * @param User $objUser
     * @param bool $bnFailure
     * @return UserContactPhone
     */
    public function find(string $phone, ?User $objUser = null, ?bool $bnFailure = false): UserContactPhone {
        if (!$objUser) {
            $objUser = Auth::user();
        }
        return ($this->phoneRepo->findByUser($phone, $objUser, $bnFailure));
    }

    /**
     * @param UserContactPhone $objPhone
     * @param User $objUser
     * @param array $arrParams
     * @return UserContactPhone
     */
    public function update(UserContactPhone $objPhone, User $objUser, array $arrParams): UserContactPhone {
        $arrPhone = [];
        if (isset($arrParams["phone_type"])) {
            $arrPhone["phone_type"] = Util::ucfLabel($arrParams["phone_type"]);
        }

        if (isset($arrParams["phone_number"])) {
            if ($this->find($arrParams["phone_number"], $objUser)) {
                throw new BadRequestHttpException("This phone number already exists.");
            }

            $arrPhone["phone_number"] = $arrParams["phone_number"];
        }

        if (isset($arrParams["flag_primary"]) && $arrParams["flag_primary"]) {
            $this->initForPrimary($objUser);
            $arrPhone["flag_primary"] = true;
        } else if (isset($arrParams["flag_primary"])) {
            $arrPhone["flag_primary"] = false;
        }

        return ($this->phoneRepo->update($objPhone, $arrPhone));
    }
}

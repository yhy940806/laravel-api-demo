<?php

namespace App\Repositories\User;

use Util;
use Illuminate\Support\Collection;
use App\Repositories\BaseRepository;
use App\Models\{User, UserContactEmail};

class UserContactEmailRepository extends BaseRepository {
    public function __construct(UserContactEmail $objEmail) {
        $this->model = $objEmail;
    }

    /**
     * @param array $arrEmail
     * @return UserContactEmail
     * @throws \Exception
     */
    public function createModel(array $arrEmail): UserContactEmail {
        $model = new UserContactEmail;
        if (!isset($arrEmail["row_uuid"]))
            $arrEmail["row_uuid"] = Util::uuid();
        $model->fill($arrEmail);
        $model->save();

        return ($model);
    }

    /**
     * @param string $strEmail
     * @param bool $bnFailure
     * @return UserContactEmail
     */
    public function find($strEmail, bool $bnFailure = false) {
        $queryBuilder = $this->model->whereRaw("lower(user_auth_email) = (?)", Util::lowerLabel($strEmail));

        if ($bnFailure) {
            return ($queryBuilder->firstOrFail());
        }

        return ($queryBuilder->first());
    }


    public function findWithTrashed($strEmail) {
        return $this->model->whereRaw("lower(user_auth_email) = (?)", Util::lowerLabel($strEmail))->withTrashed()
                           ->first();
    }

    /**
     * @param User $user
     * @return bool
     */
    public function hasPrimary(User $user): bool {
        return ($user->emails()->where("flag_primary", true)->exists());
    }

    /**
     * @param User $objUser
     * @return Collection
     */
    public function verifiedEmails(User $user): Collection {
        return ($user->emails()->whereNotNull(UserContactEmail::STAMP_EMAIL)->get());
    }

    /**
     * @param User $user
     * @return UserContactEmail|null
     */
    public function primary(User $user): ?UserContactEmail {
        return ($user->emails()->where("flag_primary", true)->first());
    }

    /**
     * @param User $user
     * @param string $hash
     * @return UserContactEmail|null
     */
    public function getEmailByVerificationHash(string $hash): ?UserContactEmail {
        return ($this->model->where("verification_hash", $hash)->first());
    }

    public function verifyEmail(UserContactEmail $userEmail): UserContactEmail {
        $userEmail->verified();

        return $userEmail;
    }
}

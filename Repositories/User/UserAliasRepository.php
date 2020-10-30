<?php

namespace App\Repositories\User;

use Util;
use App\Models\{User, UserAuthAlias};
use App\Repositories\BaseRepository;

class UserAliasRepository extends BaseRepository {
    /**
     * @param UserAuthAlias $objAuthAlias
     * @return void
     */
    public function __construct(UserAuthAlias $objAuthAlias) {
        $this->model = $objAuthAlias;
    }

    /**
     * @param $id
     * @param bool $bnFailure
     * @return UserAuthAlias
     */
    public function find($id, ?bool $bnFailure = false): UserAuthAlias {
        $queryBuilder = $this->model->whereRaw("lower(user_alias) = (?)", Util::lowerLabel($id));
        if ($bnFailure) {
            return ($queryBuilder->firstOrFail());
        } else {
            return ($queryBuilder->first());
        }
    }

    /**
     * @param User $user
     * @param bool $bnFailure
     * @return UserAuthAlias
     */
    public function findPrimary(User $user, bool $bnFailure = false): ?UserAuthAlias {
        $queryBuilder = $user->aliases()->where("flag_primary", true);
        if ($bnFailure) {
            return ($queryBuilder->firstOrFail());
        } else {
            return ($queryBuilder->first());
        }
    }
}

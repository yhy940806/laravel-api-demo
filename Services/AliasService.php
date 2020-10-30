<?php

namespace App\Services;

use Util;
use App\Models\{User, UserAuthAlias};
use App\Repositories\User\UserAliasRepository;
use Illuminate\Support\Collection as SupportCollection;

class AliasService {
    /** @var UserAliasRepository */
    protected UserAliasRepository $aliasRepo;

    /**
     * @param UserAliasRepository $aliasRepo
     */
    public function __construct(UserAliasRepository $aliasRepo) {
        $this->aliasRepo = $aliasRepo;
    }

    /**
     * @param string $strAlias
     * @param bool $bnFailure
     * @return UserAuthAlias
     */
    public function find(string $strAlias, bool $bnFailure = false): UserAuthAlias {
        $objAlias = UserAuthAlias::whereRaw("lower(user_alias) = (?)", Util::lowerLabel($strAlias))->firstOrFail();

        return ($objAlias);
    }

    /**
     * @param string $likeAlias
     * @return SupportCollection
     */
    public function findAliases(string $likeAlias): SupportCollection {
        return (UserAuthAlias::whereRaw("lower(user_alias) like (?)", "%" . $likeAlias . "%")->get());
    }

    /**
     * @param User $user
     * @return UserAuthAlias|null
     */
    public function primary(User $user): ?UserAuthAlias {
        return ($this->aliasRepo->findPrimary($user));
    }
}

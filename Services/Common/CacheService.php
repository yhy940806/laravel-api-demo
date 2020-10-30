<?php

namespace App\Services\Common;

use Cache;
use Illuminate\Support\Collection;
use App\Repositories\User\UserRepository;

class CacheService {
    /** @var UserRepository */
    protected UserRepository $userRepo;

    /**
     * @param UserRepository $userRepo
     */
    public function __construct(UserRepository $userRepo) {
        $this->userRepo = $userRepo;
    }

    /**
     * @param int $lastUserId
     * @return void
     */
    public function cache(int $lastUserId) {
        $arrUsers = $this->userRepo->findAllAfter($lastUserId);

        $this->cachingUsers($arrUsers);
    }

    /**
     * @param Collection $arrUsers
     * @return void
     */
    public function cachingUsers(Collection $arrUsers) {
        foreach ($arrUsers as $objUser) {
            Cache::rememberForever("users.user_id." . $objUser->user_id, function () use ($objUser) {
                return ([
                    "uuid"        => $objUser->user_uuid,
                    "name_first"  => $objUser->name_first,
                    "name_middle" => $objUser->name_middle,
                    "name_last"   => $objUser->name_last,
                ]);
            });
        }

        $objLastUser = $this->userRepo->getLast();
        $lastUserId = $objLastUser ? $objLastUser->user_id : 0;

        Cache::rememberForever("job.users.user_id", function () use ($lastUserId) {
            return ($lastUserId);
        });
    }
}

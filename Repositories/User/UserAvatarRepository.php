<?php

namespace App\Repositories\User;

use App\Models\UserAvatar;
use App\Repositories\BaseRepository;

class UserAvatarRepository extends BaseRepository {
    public function __construct(UserAvatar $objUser) {
        $this->model = $objUser;
    }

    /**
     * @param int $intUserId
     * @return
     */
    public function getModel(int $intUserId) {
        $objUserAvatar = $this->model->where("user_id", $intUserId)->where("flag_active", true)->first();

        return ($objUserAvatar);
    }

    /**
     * @param $objUser
     * @param $strFileName
     * @return UserAvatar
     * @throws \Exception
     */
    public function createModel($objUser, $strFileName) {
        $objUser->avatar()->update([
            "flag_active" => false,
        ]);

        $model = $objUser->avatar()->create([
            "user_id"     => $objUser->user_id,
            "user_uuid"   => $objUser->user_uuid,
            "file_name"   => $strFileName,
            "avatar_uuid" => \App\Helpers\Util::uuid(),
            "flag_active" => true,
        ]);

        return ($model);
    }
}
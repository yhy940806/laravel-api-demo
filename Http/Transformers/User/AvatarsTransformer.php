<?php

namespace App\Http\Transformers\User;

use App\Models\User;
use App\Helpers\Constant;
use App\Traits\StampCache;
use App\Http\Transformers\BaseTransformer;

class AvatarsTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(User $objUsers)
    {
        $response = [
            "user_uuid"  => $objUsers->user_uuid,
            "avatar_url" => $objUsers->flag_avatar ?
                            "users" . Constant::Separator . "avatars" . Constant::Separator . $objUsers->user_uuid . ".png" :
                            "users" . Constant::Separator . "avatars" . Constant::Separator . "default.png"
        ];

        return($response);
    }
}

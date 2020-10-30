<?php

namespace App\Http\Transformers\User;

use Util;
use App\Models\User;
use App\Traits\StampCache;
use App\Http\Transformers\BaseTransformer;

class AvatarTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(User $objUser)
    {
        $response = [
            "avatar_url"  => Util::avatar_url($objUser),
            "avatar_path" => Util::getAvatarPath($objUser)
        ];
        $response = array_merge($response, $this->stamp($objUser));

        return($response);
    }
}

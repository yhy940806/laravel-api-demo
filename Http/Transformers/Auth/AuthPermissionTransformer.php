<?php

namespace App\Http\Transformers\Auth;

use App\Http\Transformers\BaseTransformer;
use App\Models\Core\Auth\AuthPermission;
use App\Traits\StampCache;

class AuthPermissionTransformer extends BaseTransformer
{

    use StampCache;
    public function transform(AuthPermission $objAuthPerm)
    {

        $response = [
            "permission_uuid" => $objAuthPerm->permission_uuid,
            "permission_name" => $objAuthPerm->permission_name,
            "permission_memo" => $objAuthPerm->permission_memo,
        ];

        if (isset($objAuthPerm->pivot))
        {
            $response["permission_value"] = $objAuthPerm->pivot->permission_value;
        }
        $response = array_merge($response, $this->stamp($objAuthPerm));

        return($response);
    }
}

<?php

namespace App\Http\Transformers\Auth;

use App\Http\Transformers\User\UserTransformer;
use App\Models\{Core\Auth\AuthGroup, Core\Auth\AuthPermission, BaseModel, User};
use Cache;
use League\Fractal\TransformerAbstract;

class AuthGroupTransformer extends TransformerAbstract
{

    protected $cacheTTL;

    public $availableIncludes = [

    ];

    protected $defaultIncludes = [

    ];

    protected $objPerm;

    public function __construct($arrIncludes = null, AuthPermission $objPerm = null)
    {
        $this->cacheTTL = config("constant.cache_ttl");

        $this->objPerm = $objPerm;

        if ($arrIncludes)
        {
            foreach($arrIncludes as $item)
            {
                $item = strtolower($item);
                $this->availableIncludes []= $item;
                $this->defaultIncludes []= $item;
            }
        }

    }

    public function transform(AuthGroup $objAuthGroup)
    {
        $res = [
            "group_uuid" => $objAuthGroup->group_uuid,
            "auth_uuid" => $objAuthGroup->auth_uuid,
            "group_name" => $objAuthGroup->group_name,
            "group_memo" => $objAuthGroup->group_memo,
            BaseModel::STAMP_CREATED => $objAuthGroup->stamp_created,
            BaseModel::STAMP_CREATED_BY => $objAuthGroup->{BaseModel::STAMP_CREATED_BY},
            BaseModel::STAMP_UPDATED => $objAuthGroup->stamp_updated,
            BaseModel::STAMP_UPDATED_BY => $objAuthGroup->{BaseModel::STAMP_UPDATED_BY}
        ];

        if (isset($objAuthGroup->pivot->permission_uuid))
        {
            $res["permission"] = [
                "data" => [
                    "permission_uuid" => $objAuthGroup->pivot->permission_uuid,
                    "permission_value" => $objAuthGroup->pivot->permission_value,
                ]
            ];
        }
        return($res);
    }

    public function includeUsers(AuthGroup $objAuthGroup)
    {
        return($this->collection($objAuthGroup->users, new UserTransformer(["aliases", "emails", "avatar"])));
    }

    public function includePermissions(AuthGroup $objAuthGroup)
    {
        if (!$this->objPerm)
        {
            return($this->collection($objAuthGroup->permissions, new AuthPermissionTransformer()));
        } else {
            return($this->item($objAuthGroup->permissions()
                ->wherePivot("permission_id", $this->objPerm->permission_id)
                ->first(), new AuthPermissionTransformer()));
        }

    }

}

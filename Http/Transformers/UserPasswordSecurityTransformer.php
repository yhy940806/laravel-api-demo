<?php

namespace App\Http\Transformers;

use App\Models\UserPasswordSecurity;
use League\Fractal\TransformerAbstract;

class UserPasswordSecurityTransformer extends TransformerAbstract
{
    public $availableIncludes = [

    ];

    protected $defaultIncludes = [

    ];

    public function transform(UserPasswordSecurity $objUserPasswordSecurity)
    {
        return([
            "security_uuid" => $objUserPasswordSecurity->security_uuid,
            "user_uuid" => $objUserPasswordSecurity->user_uuid,
            "google2fa_enable" => $objUserPasswordSecurity->google2fa_enable,
        ]);
    }
}

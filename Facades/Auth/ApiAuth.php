<?php

namespace App\Facades\Auth;
use Illuminate\Support\Facades\Auth;

class ApiAuth extends Auth
{

    /**
     * @return string|null
     */
    public static function uuid()
    {
        $user = Auth::user();
        return $user ? $user->user_uuid : null;
    }
}


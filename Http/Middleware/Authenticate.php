<?php

namespace App\Http\Middleware;

use Exception;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use App\Traits\Respond;

class Authenticate extends Middleware
{

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate($request, array $guards)
    {
        try{
            parent::authenticate($request, $guards);
        } catch (Exception $e) {
            abort(401, "Unauthorized Token");
        }
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('signin');
        }
    }
}

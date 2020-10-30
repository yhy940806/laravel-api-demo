<?php

namespace App\Http\Middleware;

use Closure;

class SetHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIyIiwianRpIjoiNzRjOTk5YjBhMTBmYWMxYzBmYWExYmUxOTMwNTg1NTYxOTRhYTdmYWVmYWU5MGFjNDU3YWZmMmIyMGEyOTdhODhlODA5NTE4ODE3MjRlOTUiLCJpYXQiOjE1ODY5MjIzMDEsIm5iZiI6MTU4NjkyMjMwMSwiZXhwIjoxNTg3MDA4NzAxLCJzdWIiOiIzIiwic2NvcGVzIjpbXX0.mg2VwK4HY_U0a1QTVSRLTEln7NScB9gFxLFQjJFxm6uls_Tvky-t7w0qIa4LEKVyBLeB1Zz-nvQ5qOXOn23koO_VPeWWehXYKXKKNsWlymWV5SQagD8fKUKYMfaiu3otXAJb1ulcMN9P4Hu5BaycDMJTt-hS5Qs2rmBmMnNcH29pV24VxJIn7RgTjkpUinLCeg7LMdKDVvpQJ_tsjPiaZu99UNg7YfUDXPGRqtvnhTOh7TRpKd2PKaaeiirjhV1kUywfAp8NhQKXsy8eL6vHdVskP1czDx4m42ZK6vHjfWpGAYVwDdTMr-TEmA2NCD5-giIau0sL0eam-zdM0ca9_rWr_IS99zUQUqH05VuMhgukcwy73ehkn1ya44K0FCCsPcdmHDbrpeW6AJWRPjkh716moEJXJ4cdQZi0q0KM9wsCoxbr4UlIjfB6agYOd3KIWicr8Tnjdvf-k1a3yTD6yVVm9R_dxpCZM-6lTKhkfYs-0VzMJe1RJ62iucy3BNzsFgGYLsHgEybvBIhqB48P_6PtGdbukP5nmROQD8JvCHjZp_oPVbkazj_lYRHR9Ej5Yop_GaLWuP20ikijVsvh1VRgAcmWsynjbeGqlIM2rk4jJoBf3T_akRGFF0Yd0v6IJdRC1OTjU3a8w4Bv1ekWh9BpNId5qN3tZgweXtAQ6W4";
        $request->headers->set("Authorization", "Bearer {$token}");
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Traits\Response;
use Closure;
use Client;

class CheckHeader
{
    use Response;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!isset($_SERVER["HTTP_X_API"]) || !isset($_SERVER["HTTP_X_API_HOST"]))
        {
            return($this->failure("HEADER Exception", "Please set API HEADER"));
        }

        $apiHeader = $_SERVER["HTTP_X_API"];
        $hostHeader = $_SERVER["HTTP_X_API_HOST"];

        if (!Client::validateHeader($apiHeader) || !Client::validateHostHeader($hostHeader))
        {
            return($this->failure("HEADER Exception", "Invalid API HEADER"));
        }
        return $next($request);
    }
}

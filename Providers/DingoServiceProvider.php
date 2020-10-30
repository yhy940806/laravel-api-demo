<?php

namespace App\Providers;

use App\Exceptions\ApiHandler;
use Dingo\Api\Provider\DingoServiceProvider as __DingoServiceProvider;

class DingoServiceProvider extends __DingoServiceProvider {
    protected function registerExceptionHandler() {
        $this->app->singleton("api.exception", function ($app) {
            return new ApiHandler($app["Illuminate\Contracts\Debug\ExceptionHandler"], $this->config("errorFormat"), $this->config("debug"));
        });
    }
}

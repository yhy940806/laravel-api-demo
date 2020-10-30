<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class UtilServiceProvider extends ServiceProvider {
    /**
     * Register services.
     *
     * @return void
     */
    public function register() {
        require_once app_path() . "/Helpers/Util.php";
        require_once app_path() . "/Helpers/Client.php";
        require_once app_path() . "/Helpers/helpers.php";
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot() {
        //
    }
}

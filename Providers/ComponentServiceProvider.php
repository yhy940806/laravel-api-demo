<?php

namespace App\Providers;

use Blade;
use Illuminate\Support\ServiceProvider;
use App\View\Components\Mail\{HeadLine, Paragraph, Button, Apparel\Subject};

class ComponentServiceProvider extends ServiceProvider {
    /**
     * Register services.
     *
     * @return void
     */
    public function register() {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot() {
        Blade::component("head-line", HeadLine::class);
        Blade::component("p", Paragraph::class);
        Blade::component("button", Button::class);
        Blade::component("subject", Subject::class);
    }
}

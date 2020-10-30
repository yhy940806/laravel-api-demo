<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\{Common\QueueJob, UserContactEmail};
use App\Observers\Common\{QueueJobObserver, EmailObserver};

class OberserServiceProvider extends ServiceProvider {
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
        QueueJob::observe(QueueJobObserver::class);
        UserContactEmail::observe(EmailObserver::class);
    }
}

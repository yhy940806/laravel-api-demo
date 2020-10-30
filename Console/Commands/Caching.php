<?php

namespace App\Console\Commands;

use App\Services\Common\CacheService;
use Illuminate\Console\Command;
use Log;
use Cache;

class Caching extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "caching";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "caching users";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param CacheService $cacheService
     * @return mixed
     */
    public function handle(CacheService $cacheService) {
        if (Cache::has("job.users.user_id")) {
            $lastUserId = Cache::get("job.users.user_id");
        } else {
            $lastUserId = 0;
        }

        Log::debug("Cache:LastUserID ---> " . $lastUserId . "  -  " . microtime(true));

        $cacheService->cache($lastUserId);
    }
}

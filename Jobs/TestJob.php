<?php

namespace App\Jobs;

use Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class TestJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;

    /**
     * Create a new job instance.
     *
     * @param string $filePath
     */
    public function __construct(string $filePath) {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        Log::info("file-contents", ["contents" => md5(file_get_contents($this->filePath))]);
    }
}

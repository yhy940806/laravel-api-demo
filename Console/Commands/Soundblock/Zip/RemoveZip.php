<?php

namespace App\Console\Commands\Soundblock\Zip;

use App\Services\Common\QueueJobService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RemoveZip extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "soundblock:zip:remove";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command description";

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
     * @param QueueJobService $jobService
     * @return mixed
     */
    public function handle(QueueJobService $jobService) {
        $jobs = $jobService->getJobsForRemove();

        foreach ($jobs as $job) {
            if (isset($job->job_json["path"])) {
                Storage::disk("s3-soundblock")->delete($job->job_json["path"]);
            }

            $job->flag_remove_file = false;
            $job->save();
        }
    }
}

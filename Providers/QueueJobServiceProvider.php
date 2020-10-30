<?php

namespace App\Providers;

use Queue;
use Illuminate\Support\ServiceProvider;
use App\Services\Common\QueueJobService;
use Illuminate\Queue\Events\{JobFailed, JobProcessed, JobProcessing};

class QueueJobServiceProvider extends ServiceProvider {
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
        /** @var QueueJobService */
        $queueJobService = app(QueueJobService::class);
        Queue::before(function (JobProcessing $event) {
            echo "Before processing time... " . microtime(true) . PHP_EOL;
            echo $event->job->payload()["displayName"] . PHP_EOL;
        });

        // handle the event after the job is processed in the queue.
        Queue::after(function (JobProcessed $event) use ($queueJobService) {
            echo "Processed time... " . microtime(true) . PHP_EOL;
            $job = $event->job;

            $jobId = $job->getJobId();

            if (is_int($jobId)) {
                $queueJob = $queueJobService->findByJobId($jobId);

                if ($queueJob) {
                    $queueJob->released();
                }
            }


        });

        // For handling the failed jobs in the future...
        Queue::failing(function (JobFailed $event) use ($queueJobService) {
            $job = $event->job;
            $queueJob = $queueJobService->findByJobId($job->getJobId());
            echo "Job failed" . PHP_EOL;
            if ($queueJob) {
                $queueJob->failed();
            }
        });
    }
}

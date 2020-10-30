<?php

namespace App\Jobs\Zip;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Common\{ZipService, QueueJobService};
use App\Models\{Common\QueueJob, Soundblock\Collection};
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class ExtractCollectionJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $storagepath;

    protected Collection $objCol;

    protected QueueJob $objQueueJob;

    /**
     * Create a new job instance.
     *
     * @param string $storagepath
     * @param Collection $objCol
     * @param QueueJob $objQueueJob
     */
    public function __construct(string $storagepath, Collection $objCol, QueueJob $objQueueJob) {
        $this->storagepath = $storagepath;
        $this->objCol = $objCol;
        $this->objQueueJob = $objQueueJob;
    }

    /**
     * Execute the job.
     *
     * @param ZipService $zipService
     * @param QueueJobService $qjService
     * @return void
     */
    public function handle(ZipService $zipService, QueueJobService $qjService) {
        $arrQJParams = [
            "queue_id" => $this->job->getJobId(),
            "job_type" => "Job.Soundblock.Extract",
            "job_name" => $this->job->payload()["displayName"],
        ];
        $qjService->update($this->objQueueJob, $arrQJParams);

        $zipService->unzipCollection($this->storagepath, $this->objCol);
    }
}

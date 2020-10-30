<?php

namespace App\Jobs\Zip;

use Builder;
use Storage;
use Illuminate\Bus\Queueable;
use App\Events\Common\PrivateNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use App\Models\{Core\App, Common\QueueJob, Soundblock\Collection};
use App\Services\{AliasService, Common\QueueJobService, Common\ZipService};

class ZipJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var QueueJob $queueJob */
    protected $queueJob;
    /** @var Collection $collection */
    protected $collection;
    /** @var SupportCollection $files */
    protected $files;

    /**
     * Create a new job instance.
     * @param QueueJob $queueJob
     * @param Collection $collection
     * @param SupportCollection $files
     */
    public function __construct(QueueJob $queueJob, Collection $collection, ?SupportCollection $files = null) {
        $this->queueJob = $queueJob;
        $this->collection = $collection;
        $this->files = $files;
    }

    /**
     * Execute the job.
     * @param ZipService $zipService
     * @param QueueJobService $qjService
     * @param AliasService $aliasService
     * @return void
     */
    public function handle(ZipService $zipService, QueueJobService $qjService, AliasService $aliasService) {
        try {
            if (env("APP_ENV") == "local") {
                /** @var \Illuminate\Filesystem\FilesystemAdapter */
                $soundblockAdatper = Storage::disk("local");
            } else {
                /** @var \Illuminate\Filesystem\FilesystemAdapter */
                $soundblockAdatper = Storage::disk("s3-soundblock");
            }
            echo "Handling time... " . microtime(true) . PHP_EOL;
            $queueJobParams = [
                "queue_id" => is_null($this->job) ? null : $this->job->getJobId(),
                "job_name" => is_null($this->job) ? null : $this->job->payload()["displayName"],
            ];

            $queueJob = $qjService->update($this->queueJob, $queueJobParams);

            if (is_null($this->files) || $this->files->isEmpty()) {
                $zipFilePath = $zipService->zipCollection($this->collection);
            } else {
                $zipFilePath = $zipService->zipFiles($this->collection, $this->files);
            }
            /** @var string */
            $zipUrl = $soundblockAdatper->url($zipFilePath);
            echo "Zip URL ===> " . $zipUrl . PHP_EOL;

            if ($zipFilePath) {
                $queueJob = $qjService->update($queueJob, [
                    "job_json" => [
                        "download" => $zipUrl,
                        "path" => $zipFilePath
                    ],
                ]);

                $downloadLink = url("soundblock/project/collection/download/zip", ["jobUuid" => $queueJob->job_uuid]);

                echo "Download URL ===> " . $downloadLink . PHP_EOL;

                $qjService->update($queueJob, $queueJobParams);

                if ($queueJob->flag_silentalert == 0) {
                    $alias = $aliasService->primary($queueJob->user);
                    $contents = [
                        "notification_name"   => "Hello",
                        "notification_memo"   => "All files zipped successfully.",
                        "notification_action" => Builder::notification_link(["url" => $downloadLink, "link_name" => "Download"]),
                        "message"             => sprintf("Mr %s All files zipped.", $alias->user_alias),
                        "alias"               => $alias->user_alias,
                    ];

                    $flags = [
                        "notification_state" => "unread",
                        "flag_canarchive"    => true,
                        "flag_candelete"     => true,
                        "flag_email"         => false,
                    ];

                    event(new PrivateNotification($queueJob->user, $contents, $flags, $queueJob->app));
                }

            }
        } catch (\Exception $exception) {
            dd($exception);
        }

    }
}

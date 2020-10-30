<?php

namespace App\Listeners\Common;

use Log;
use Builder;
use Throwable;
use Illuminate\Contracts\Queue\Job;
use App\Events\Common\PrivateNotification;
use Illuminate\Queue\Events\JobExceptionOccurred;
use App\Services\Common\{QueueJobService, NotificationService};
use App\Services\{AliasService, Soundblock\ProjectService, Soundblock\CollectionService};
use App\Models\{Soundblock\Collection, Soundblock\Project, User, UserAuthAlias, Core\App, Common\QueueJob};

class JobExceptionOccuredListener {
    /** @var QueueJobService */
    protected QueueJobService $queueJobService;
    /** @var NotificationService */
    protected NotificationService $notificationService;
    /** @var AliasService */
    protected AliasService $aliasService;
    /**
     * @var ProjectService
     */
    protected ProjectService $projectService;
    /**
     * @var CollectionService
     */
    protected CollectionService $collectionService;

    /**
     * Create the event listener.
     * @param QueueJobService $queueJobService
     * @param NotificationService $notificationService
     * @param AliasService $aliasService
     * @param ProjectService $projectService
     * @param CollectionService $collectionService
     *
     */
    public function __construct(QueueJobService $queueJobService, NotificationService $notificationService, AliasService $aliasService, ProjectService $projectService, CollectionService $collectionService) {
        $this->queueJobService = $queueJobService;
        $this->notificationService = $notificationService;
        $this->aliasService = $aliasService;
        $this->projectService = $projectService;
        $this->collectionService = $collectionService;
    }

    /**
     * Handle the event.
     *
     * @param JobExceptionOccurred $event
     *
     * @return void
     * @throws \Exception
     */
    public function handle($event) {
        /** @var string */
        $connectionName = $event->connectionName;
        $job = $event->job;
        /** @var Throwable */
        $exception = $event->exception;
        $queueJob = $this->storeException($connectionName, $job, $exception);
        if ($queueJob) {
            $this->sendNotification($queueJob);
        }
    }

    /**
     * @param string $connectionName
     * @param Job $job
     * @param Throwable $exception
     * @return QueueJob|null
     */
    private function storeException(string $connectionName, Job $job, Throwable $exception): ?QueueJob {
        /** @var QueueJob */
        $queueJob = $this->queueJobService->findByJobId($job->getJobId(), false);
        Log::info("Job Exception Occured", [$queueJob]);
        if (!$queueJob)
            return (null);
        $exceptionContents = [
            "connection_name" => $connectionName,
            "exception"       => [
                "class"     => get_class($exception),
                "code"      => $exception->getCode(),
                "message"   => $exception->getMessage(),
                "throwable" => $exception,
                "trace"     => $exception->getTraceAsString(),
            ],
        ];
        $this->processRefCollection($queueJob);
        $queueJob = $this->queueJobService->update($queueJob, ["job_json" => $exceptionContents]);
        return ($queueJob);
    }

    /**
     * @param QueueJob $queueJob
     *
     * @return void
     */
    public function processRefCollection(QueueJob $queueJob) {
        $jobJson = $queueJob->job_json;
        if (!isset($jobJson["project"]))
            return;
        /** @var Project */
        $project = $this->projectService->find($jobJson["project"]);
        /** @var Collection */
        $latestCollection = $this->collectionService->findLatestByProject($project);
        if (!$latestCollection->history) {
            $latestCollection->files()->detach();
            $latestCollection->forceDelete();
        }
    }

    /**
     * @param QueueJob $queueJob
     * @return void
     * @throws \Exception
     */
    private function sendNotification(QueueJob $queueJob) {
        /** @var User $user */
        $user = $queueJob->user;
        /** @var App $app */
        $app = $queueJob->app;
        /** @var array $jobJson */
        $jobJson = $queueJob->job_json;
        /** @var UserAuthAlias */
        $alias = $this->aliasService->primary($user);
        $notificationParams = [
            "notification_name"   => sprintf("Failed Job"),
            "notification_memo"   => sprintf("Occurred Exception (%s)", $jobJson["exception"]["message"]),
            "notification_action" => "",
            "message"             => $jobJson["exception"]["message"],
            "alias"               => $alias->user_alias,
        ];
        $userNotificationParams = [
            "notification_state" => "unread",
            "flag_canarchive"    => true,
            "flag_candelete"     => true,
            "flag_email"         => false,
        ];
        $flags = [
            "notification_state" => "unread",
            "flag_canarchive"    => true,
            "flag_candelete"     => true,
            "flag_email"         => false,
        ];
        $notification = $this->notificationService->create($notificationParams, $user, $app);
        $this->notificationService->attachUser($notification, $user, $userNotificationParams);

        event(new PrivateNotification($user, $notificationParams, $flags, $app));
    }
}

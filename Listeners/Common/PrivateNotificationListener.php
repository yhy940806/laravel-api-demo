<?php

namespace App\Listeners\Common;

use App\Events\Common\PrivateNotification;
use App\Events\Common\UserNotification;
use App\Services\Common\NotificationService;
use App\Models\{User, Core\App};

class PrivateNotificationListener {
    protected NotificationService $notificationService;

    /**
     * Create the event listener.
     * @param NotificationService $notificationService
     * @return void
     */
    public function __construct(NotificationService $notificationService) {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     * @throws \Exception
     */
    public function handle(PrivateNotification $event) {
        /** @var User */
        $receiver = $event->receiver;
        /** @var array */
        $contents = $event->contents;
        /** @var array */
        $flags = $event->flags;
        /** @var App */
        $app = $event->app;

        $notification = $this->notificationService->create($contents, $receiver, $app);
        $this->notificationService->attachUser($notification, $receiver, $flags);
        $contents = array_merge($contents, ["notification_uuid" => $notification->notification_uuid]);
        $notificationSetting = $this->notificationService->findSetting($receiver);
        $userSetting = $notificationSetting->where("app_id", $app->app_id)->first();
        $authNames = $this->notificationService->getAllowedApps($userSetting);

        event(new UserNotification($contents, $receiver, $authNames));
    }
}

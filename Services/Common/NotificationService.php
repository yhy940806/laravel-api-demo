<?php

namespace App\Services\Common;


use Util;
use Auth;
use Client;
use Exception;
use App\Repositories\{Core\Auth\AuthGroupRepository,
    User\UserRepository,
    Common\AppRepository,
    Common\NotificationRepository,
    Common\NotificationSettingRepository
};
use App\Models\{BaseModel, Core\App, Notification, NotificationSetting, User};

class NotificationService {
    /** @var AppRepository */
    protected AppRepository $appRepo;
    /** @var UserRepository */
    protected UserRepository $userRepo;
    /** @var AuthGroupRepository */
    protected AuthGroupRepository $authGroupRepo;
    /** @var NotificationRepository */
    protected NotificationRepository $notiRepo;
    /** @var NotificationSettingRepository */
    protected NotificationSettingRepository $settingRepo;

    /**
     * @param AppRepository $appRepo
     * @param UserRepository $userRepo
     * @param AuthGroupRepository $authGroupRepo
     * @param NotificationRepository $notiRepo
     * @param NotificationSettingRepository $settingRepo
     *
     * @return void
     */
    public function __construct(
        NotificationRepository $notiRepo,
        AppRepository $appRepo,
        UserRepository $userRepo,
        AuthGroupRepository $authGroupRepo,
        NotificationSettingRepository $settingRepo
    ) {
        $this->appRepo = $appRepo;
        $this->userRepo = $userRepo;
        $this->authGroupRepo = $authGroupRepo;
        $this->notiRepo = $notiRepo;
        $this->settingRepo = $settingRepo;
    }

    /**
     * @param Notification $notification
     * @param User $user
     * @param array $params
     * @return Notification
     */
    public function attachUser(Notification $notification, User $user, ?array $params = null): Notification {
        return ($this->notiRepo->attachUser($notification, $user, $params));
    }

    /**
     * @param int/string $id
     * @param bool $bnFailure
     * @return Notification
     * @throws Exception
     */
    public function find($id, ?bool $bnFailure = true) {
        return ($this->notiRepo->find($id, $bnFailure));
    }

    /**
     * @param array $reqParams
     * @param string $user
     * @return \Illuminate\Contracts\Pagination\Paginator
     * @throws Exception
     */
    public function findAllByUser(array $reqParams = [], ?string $user = null) {
        /** @var User $objUser */
        if (is_null($user)) {
            /** @var User */
            $objUser = Auth::user();
        } else {
            /** @var User */
            $objUser = $this->userRepo->find($user, true);
        }

        return ($this->notiRepo->findAllByUser($objUser, $reqParams));
    }

    /**
     * @param string $status
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findAllByStatus(string $status, ?User $user = null): \Illuminate\Database\Eloquent\Collection {
        if (is_null($user)) {
            $user = Auth::user();
        }
        return ($this->notiRepo->findAllByStatus($status, $user));
    }

    /**
     * @param array $arrParams
     * @param User|null $user
     * @param App|null $app
     * @return Notification
     * @throws Exception
     *
     */
    public function create(array $arrParams, ?User $user = null, ?App $app = null): Notification {
        $notificationParams = [];
        if (!Util::array_keys_exists(["notification_name"], $arrParams))
            throw new Exception("Invalid Parameter.", 400);
        if (!isset($user)) {
            if (is_null(Auth::user()))
                throw new Exception("Something went wrong.", 400);
            /** @var User $user */
            $user = Auth::user();
        }
        if (!isset($app)) {
            if (is_null(Client::app()))
                throw new Exception("Something went wrong.", 400);
            /** @var App $app */
            $app = Client::app();
        }
        $notificationParams = collect($arrParams)->only("notification_name", "notification_memo", "notification_action")
                                                 ->toArray();

        if (!isset($notificationParams["notification_memo"])) {
            $notificationParams["notification_memo"] = sprintf("This notification was sent by the user (%s)", $user->user_uuid);
        }
        if (!isset($notificationParams["notification_action"])) {
            $notificationParams["notification_action"] = "Default Action";
        }

        $notificationParams = array_merge($notificationParams, [
            "app_id"                       => $app->app_id,
            "app_uuid"                     => $app->app_uuid,
            Notification::STAMP_CREATED_BY => $user->user_id,
            Notification::STAMP_UPDATED_BY => $user->user_id,
        ]);
        $notification = $this->notiRepo->create($notificationParams);

        return ($notification);
    }

    /**
     * @param Notification $objNoti
     * @return bool
     */
    public function archives(Notification $objNoti): bool {
        return ($this->updateUserNotification(Auth::user(), $objNoti, ["flag_canarchive" => false]));
    }

    /**
     * @param User $objUser
     * @param Notification $objNoti
     * @param array $arrParams
     * @return bool
     */
    public function updateUserNotification(User $objUser, Notification $objNoti, array $arrParams): bool {
        $arrNoti = collect($arrParams)->only(["flag_candelete", "flag_canarchive", "notification_state"])
                                      ->reject(function ($value, $key) {
                                          return (is_null($value));
                                      })->toArray();
        return ($this->notiRepo->updateUserNotification($objUser, $objNoti, $arrNoti));
    }

    /**
     * @param array $notifications
     */
    public function archive(array $notifications): int {
        return ($this->notiRepo->archive(Auth::user(), $notifications));
    }

    /**
     * @param array $notifications
     * @return int
     */
    public function delete(array $notifications): int {
        return ($this->notiRepo->delete(Auth::user(), $notifications));
    }

    /**
     * @param Notification $objNoti
     * @return bool
     */
    public function read(Notification $objNoti) {
        return ($this->updateUserNotification(Auth::user(), $objNoti, ["notification_state" => "read"]));
    }

    /**
     * @param User $objUser
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findSetting(?User $objUser = null): \Illuminate\Database\Eloquent\Collection {
        if (is_null($objUser)) {
            /** @var User */
            $objUser = Auth::user();
        }
        if ($objUser->notificationSettings->isEmpty())
            return ($this->createSetting($objUser));

        return ($objUser->notificationSettings);
    }

    /**
     * @param User $objUser
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createSetting(?User $user = null): \Illuminate\Database\Eloquent\Collection {
        if (is_null($user)) {
            /** @var User */
            $user = Auth::user();
        }
        if ($user->notificationSettings->isNotEmpty()) {
            return ($user->notificationSettings);
        }

        $setting = [
            "user_id"                   => $user->user_id,
            "user_uuid"                 => $user->user_uuid,
            "user_setting"              => config("constant.notification.setting"),
            BaseModel::STAMP_CREATED_BY => $user->{BaseModel::STAMP_CREATED_BY},
            BaseModel::STAMP_UPDATED_BY => $user->{BaseModel::STAMP_UPDATED_BY},
        ];
        $apps = $this->appRepo->findAll();

        foreach ($apps as $app) {
            $appSettings = [
                "app_id"                => $app->app_id,
                "app_uuid"              => $app->app_uuid,
                "flag_{$app->app_name}" => true,
            ];

            $this->settingRepo->create(array_merge($setting, $appSettings));
        }

        return ($user->notificationSettings()->get());
    }

    /**
     * @param User $objUser
     * @param User $objApp
     *
     * @return NotificationSetting
     */
    public function findSettingByApp(User $objUser, App $objApp): NotificationSetting {
        return ($objUser->notificationSettings()->where("app_id", $objApp->app_id)->first());
    }

    /**
     * @param Notification $objSetting
     * @param array $arrParams
     * @return NotificationSetting
     */
    public function updateSetting(NotificationSetting $objSetting, array $arrParams): NotificationSetting {
        $arrUserSetting = $objSetting->user_setting;
        $apps = $this->appRepo->findAll();

        foreach ($apps as $app) {
            if (isset($arrParams["flag_{$app->app_name}"])) {
                if (array_search("flag_" . strtolower($app->app_name), NotificationSetting::APP_FLAGS) !== false) {
                    $objSetting->{"flag_" . strtolower($app->app_name)} = $arrParams["flag_{$app->app_name}"];
                }
            }
        }
        if (isset($arrParams["setting"]["position"])) {
            $platform = strtolower(Client::platform()) == "web" ? "web" : "mobile";
            $arrUserSetting["position"][$platform] = strtolower($arrParams["setting"]["position"]);
        }
        if (isset($arrParams["setting"]["per_page"])) {
            $arrUserSetting["per_page"] = intval($arrParams["setting"]["per_page"]);
        }
        if (isset($arrParams["setting"]["show_time"])) {
            $arrUserSetting["show_time"] = intval($arrParams["setting"]["show_time"]);
        }
        if (isset($arrParams["setting"]["play_sound"])) {
            $arrUserSetting["play_sound"] = intval($arrParams["setting"]["play_sound"]);
        }
        $objSetting->user_setting = $arrUserSetting;
        $objSetting->save();

        return ($objSetting);
    }

    /**
     * @param NotificationSetting $objSetting
     * @return array
     */
    public function getAllowedApps(NotificationSetting $objSetting): array {
        $arrayAllowedApps = [];
        $apps = $this->appRepo->findAll();

        foreach ($apps as $app) {
            if (isset($objSetting["flag_" . $app->app_name]) && $objSetting["flag_" . $app->app_name]) {
                $arrayAllowedApps[] = "App." . ucfirst($app->app_name);
            }
        }

        $defaultApp = "App." . ucfirst($objSetting->app()->value("app_name"));

        if (array_search($defaultApp, $arrayAllowedApps) === false) {
            $arrayAllowedApps[] = $defaultApp;
        }

        return $arrayAllowedApps;
    }
}

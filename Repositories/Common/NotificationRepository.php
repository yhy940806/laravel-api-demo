<?php

namespace App\Repositories\Common;

use Util;
use Auth;
use Client;
use Exception;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use App\Models\{BaseModel, Notification, User};
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class NotificationRepository extends BaseRepository {
    /**
     * @param Notification $notification
     * @return void
     */
    public function __construct(Notification $notification) {
        $this->model = $notification;
    }

    /**
     * @param User $user
     * @param array $reqParams
     * @param bool $isPaginator
     * @return \Illuminate\Contracts\Pagination\Paginator/SupportCollection
     */
    public function findAllByUser(User $user, array $reqParams = [], bool $isPaginator = true) {
        /** @var \Illuminate\Database\Query\Builder */
        $queryBuilder = $user->notifications();
        $objUserSetting = $user->notificationSettings()->where("app_id", Client::app()->app_id)->firstOrFail();
        $arrUserSetting = $objUserSetting->user_setting;
        if (isset($reqParams["notification_state"])) {
            $queryBuilder->whereRaw("lower(notifications_users.notification_state) = ?", strtolower($reqParams["notification_state"]));
        } else {
            $queryBuilder->whereNotIn("notifications_users.notification_state", ["archived", "deleted"]);
        }

        if (isset($reqParams["apps"])) {
            $queryBuilder->whereHas("app", function (Builder $where) use ($reqParams) {
                $apps = explode(",", $reqParams["apps"]);

                foreach ($apps as $app) {
                    $where->orWhere("app_name", $app);
                }
            });
        } else {
            $queryBuilder->whereHas("app", function ($query) {
                $query->where("core_apps.app_id", Client::app()->app_id);
            });
        }

        if ($isPaginator) {
            if (isset($arrUserSetting["per_page"]))
                $perPage = $arrUserSetting["per_page"];
            else
                $perPage = config("constant.notification.setting.per_page");
            $arrNotis = $queryBuilder->wherePivot("flag_candelete", true)->orderBy(BaseModel::STAMP_CREATED, "asc")
                                     ->paginate($perPage);
        } else {
            $arrNotis = $queryBuilder->wherePivot("flag_candelete", true)->orderBy(BaseModel::STAMP_CREATED, "asc")
                                     ->get();
        }

        return ($arrNotis);
    }

    /**
     * @param array $arrParams
     * @return Notification
     */
    public function createModel(array $arrParams) {
        $model = new Notification;
        if (!isset($arrParams[$model->uuid()])) {
            $arrParams[$model->uuid()] = Util::uuid();
        }
        if (!Util::array_keys_exists(["app_id", "app_uuid", "notification_name"], $arrParams)) {
            throw new Exception("Invalid Paramter", 400);
        }
        if (!isset($arrParams["notification_memo"])) {
            $arrParams["notification_memo"] = sprintf("Notification (%s)", $arrParams["notification_name"]);
        }
        if (!isset($arrParams["notification_action"])) {
            $arrParams["notification_action"] = "Default Action";
        }

        $model->fill($arrParams);
        $model->save();

        return ($model);
    }

    /**
     * @param Notification $notification
     * @param array $arrParams
     * @param User $user
     * @return
     */
    public function updateUserNotification(User $user, Notification $notification, array $arrParams) {
        $arrStamp = [
            BaseModel::STAMP_UPDATED    => time(),
            BaseModel::STAMP_UPDATED_BY => $user->user_id,
        ];
        $arrParams = array_merge($arrParams, $arrStamp);
        return ($user->notifications()->updateExistingPivot($notification->notification_id, $arrParams));
    }

    /**
     * @param Notification $notification
     * @param EloquentCollection $users
     * @param array $arrParams
     * @return Notification
     */
    public function attachUsers(Notification $notification, EloquentCollection $users, ?array $arrParams = null) {
        foreach ($users as $user) {
            $this->attachUser($notification, $user, $arrParams);
        }

        return ($notification);
    }

    /**
     * @param Notification $notification
     * @param User $user
     * @param array $arrParams
     * @return Notification
     */
    public function attachUser(Notification $notification, User $user, ?array $arrParams = null) {
        $arrReqProperty = ["notification_state", "flag_canarchive", "flag_candelete", "flag_email"];
        if (is_null($arrParams)) {
            $arrParams = [
                "notification_state" => "unread",
                "flag_canarchive"    => true,
                "flag_candelete"     => true,
                "flag_email"         => false,
            ];
        } else if (!Util::array_keys_exists($arrReqProperty, $arrParams)) {
            abort(400, sprintf("Following properties (%s) are required.", implode(",", $arrReqProperty)));
        }

        $notification->users()->attach($user->user_id, [
            "row_uuid"                  => Util::uuid(),
            "notification_uuid"         => $notification->notification_uuid,
            "user_uuid"                 => $user->user_uuid,
            "notification_state"        => $arrParams["notification_state"],
            "flag_canarchive"           => $arrParams["flag_canarchive"],
            "flag_candelete"            => $arrParams["flag_candelete"],
            "flag_email"                => $arrParams["flag_email"],
            BaseModel::STAMP_CREATED    => Util::current_time(),
            BaseModel::STAMP_CREATED_BY => $user->user_id,
            BaseModel::STAMP_UPDATED    => Util::current_time(),
            BaseModel::STAMP_UPDATED_BY => $user->user_id,
        ]);

        return ($notification);
    }

    /**
     * @param string $status
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findAllByStatus(string $status, User $user): \Illuminate\Database\Eloquent\Collection {
        return ($user->notifications()
                     ->whereRaw("lower(notifications_users.notification_state) = ?", strtolower($status))->get());
    }

    /**
     * @param Notification $objNoti
     * @param User $objUser
     * @param string $strState
     * @return Notification
     */
    public function markState(Notification $notification, string $strState = "read", ?User $user = null) {
        if (!$user) {
            $user = Auth::user();
        }
        return ($this->setState($notification, $user, $strState));
    }

    /**
     * @param Notification $notification
     * @param User $user
     * @param string $strState
     * @return Notification
     */
    public function setState(Notification $notification, User $user, string $strState = "read") {
        $notification->users()->updateExistingPivot($user->user_id, [
            "notification_state"        => Util::lowerLabel($strState),
            BaseModel::UPDATED_AT       => Util::now(),
            BaseModel::STAMP_UPDATED    => time(),
            BaseModel::STAMP_UPDATED_BY => Auth::id(),
        ]);

        return ($notification);
    }

    /**
     * @param User $objUser
     * @param array $notifications
     */
    public function delete(User $objUser, array $notifications): int {
        return $objUser->notifications()
                       ->newPivotStatement()
                       ->whereIn("notification_uuid", $notifications)
                       ->where("user_id", $objUser->user_id)
                       ->where("flag_candelete", true)
                       ->whereNull(BaseModel::STAMP_DELETED)
                       ->update([
                           BaseModel::DELETED_AT       => now(),
                           BaseModel::STAMP_DELETED    => time(),
                           BaseModel::STAMP_DELETED_BY => Auth::id(),
                       ]);
    }

    /**
     * @param User $objUser
     * @param array $notifications
     *
     * @return int
     */
    public function archive(User $objUser, array $notifications): int {
        return $objUser->notifications()->newPivotStatement()
                       ->whereIn("notification_uuid", $notifications)
                       ->where("flag_canarchive", true)
                       ->where("user_id", $objUser->user_id)
                       ->whereNull(BaseModel::STAMP_DELETED)
                       ->update([
                           "notification_state"        => "archive",
                           BaseModel::UPDATED_AT       => now(),
                           BaseModel::STAMP_UPDATED    => time(),
                           BaseModel::STAMP_UPDATED_BY => Auth::id(),
                       ]);

    }
}

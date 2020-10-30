<?php

namespace App\Http\Controllers\Common;

use Auth;
use Client;
use Exception;
use App\Http\Controllers\Controller;
use App\Events\Common\PrivateNotification;
use App\Services\{AuthService, Common\NotificationService};
use App\Http\Transformers\{Common\NotificationSettingTransformer, Common\NotificationTransformer};
use App\Http\Requests\Common\Notification\{OperateNotificationsRequest,
    GetNotificationsRequest,
    NotificationStateRequest,
    UpdateSettingRequest
};

/**
 * @group Notification
 */
class NotificationController extends Controller {
    /**
     * @var AuthService
     */
    protected AuthService $authService;

    /**
     * @param AuthService $authService
     * @return void
     */
    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

    /**
     * @responseFile /responses/common/notification/index.post.json
     * @queryParam apps string optional app_name, that separated by comma ","
     *
     * @param GetNotificationsRequest $objRequest
     * @param NotificationService $notiService
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function index(GetNotificationsRequest $objRequest, NotificationService $notiService) {
        try {
            $arrNotifications = $notiService->findAllByUser($objRequest->all());
            return ($this->response->paginator($arrNotifications, new NotificationTransformer));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param string $notification
     * @param NotificationService $notiService
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function archives(string $notification, NotificationService $notiService) {
        try {
            if ($notiService->archive([$notification]))
                return ($this->apiReply("archived"));
            return ($this->apiReject("failed"));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param OperateNotificationsRequest $objRequest
     * @param NotificationService $notificationService
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function archive(OperateNotificationsRequest $objRequest, NotificationService $notificationService) {
        try {
            $archivedCount = $notificationService->archive($objRequest->notifications);
            if ($archivedCount) {
                return ($this->apiReply("archived"));
            }
            return ($this->apiReject("failed"));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param OperateNotificationsRequest $objRequest
     * @param NotificationService $notiService
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function delete(OperateNotificationsRequest $objRequest, NotificationService $notiService) {
        try {
            /** @var int */
            $deletedCounts = $notiService->delete($objRequest->notifications);

            if ($deletedCounts > 0) {
                return ($this->apiReply("{$deletedCounts} notifications deleted."));
            }

            return ($this->apiReject("failed"));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param string $notification
     * @param NotificationService $notiService
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function read(string $notification, NotificationService $notiService) {
        try {
            $objNoti = $notiService->find($notification, true);
            if ($notiService->read($objNoti))
                return ($this->apiReply("read"));
            return ($this->apiReject("failed"));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param NotificationService $notiService
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function showSetting(NotificationService $notiService) {
        try {
            $objSetting = $notiService->findSettingByApp(Auth::user(), Client::app());

            return ($this->apiReply($objSetting));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @bodyParam apps.apparel bool optional Flag to enable notifications on Apparel
     * @bodyParam apps.arena bool optional Flag to enable notifications on Arena
     * @bodyParam apps.catalog bool optional Flag to enable notifications on Catalog
     * @bodyParam apps.io bool optional Flag to enable notifications on Io
     * @bodyParam apps.merchandising bool optional Flag to enable notifications on Merchandising
     * @bodyParam apps.music bool optional Flag to enable notifications on Music
     * @bodyParam apps.office bool optional Flag to enable notifications on Office
     * @bodyParam apps.soundblock bool optional Flag to enable notifications on Soundblock
     * @bodyParam play_sound bool required Flag to play sound
     * @bodyParam position string required Name of position
     *
     * @param UpdateSettingRequest $objRequest
     * @param NotificationService $notiService
     *
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function updateSetting(UpdateSettingRequest $objRequest, NotificationService $notiService) {
        try {
            $objSetting = $notiService->findSettingByApp(Auth::user(), Client::app());
            if (!$objSetting)
                abort(400, "User has not the notification setting");
            $objSetting = $notiService->updateSetting($objSetting, $objRequest->all());

            return ($this->apiReply($objSetting));
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function send() {
        $contents = [
            "notification_name"   => "Test Notification",
            "notification_action" => "",
        ];
        $flags = [
            "notification_state" => "unread",
            "flag_canarchive"    => true,
            "flag_candelete"     => true,
            "flag_email"         => false,
        ];
        event(new PrivateNotification(Auth::user(), $contents, $flags, Client::app()));

        return ($this->apiReply());
    }

    public function check() {
        return (view("pusher"));
    }

    /**
     * @responseFile /responses/common/notification/mark-state.get.json
     * @responseFile 417 /responses/common/notification/mark-state_error.get.json
     *
     * @param NotificationStateRequest $objRequest
     *
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function markState(NotificationStateRequest $objRequest) {
        try {
            $objNoti = $this->notiService->updateState($objRequest->all());
            return ($this->response->item($objNoti, new NotificationTransformer));
        } catch (Exception $e) {
            throw $e;
        }
    }
}

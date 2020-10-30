<?php

namespace App\Http\Transformers\Common;

use App\Http\Transformers\BaseTransformer;
use App\Models\Notification;
use App\Traits\StampCache;

class NotificationTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(Notification $objNoti)
    {
        $response = [
            "notification_uuid" => $objNoti->notification_uuid,
            "notification_name" => $objNoti->notification_name,
            "notification_memo" => $objNoti->notification_memo,
            "notification_action" => $objNoti->notification_action,
            "notification_detail" => $objNoti->pivot ? [
                "notification_state" => $objNoti->pivot->notification_state,
                "flag_canarchive" => $objNoti->pivot->flag_canarchive,
                "flag_candelete" => $objNoti->pivot->flag_candelete,
            ] : [],
        ];

        return(array_merge($response, $this->stamp($objNoti)));
    }

    public function includeApp(Notification $objNoti)
    {
        return($this->item($objNoti->app, new AppTransformer));
    }
}

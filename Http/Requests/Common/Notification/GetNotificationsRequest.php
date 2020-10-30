<?php

namespace App\Http\Requests\Common\Notification;

use Illuminate\Foundation\Http\FormRequest;

class GetNotificationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return (true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return ([
            //
            "apps" => "string",
            "notification_state" => ["in:" . implode(",", config("constant.notification.state"))]
        ]);
    }

    public function validationData()
    {
        $all = parent::validationData();
        if (isset($all["notification_state"])) {
            $all["notification_state"] = strtolower($all["notification_state"]);
        }

        return($all);
    }
}

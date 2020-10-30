<?php

namespace App\Http\Requests\Common\Notification;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam notification string requiired
 * @queryParam notification_state string requiired unread/read/deleted/archieved
 */

class NotificationStateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return(true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "notification" => "required|uuid",
            "notification_state" => "required|notification_state",
        ];
    }
}

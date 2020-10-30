<?php

namespace App\Http\Requests\Soundblock\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Client;

/**
 * @queryParam user_auth_email email required
 */

class DeleteEmailRequest extends FormRequest
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
        return([

            "user_auth_email" => "required|email",
        ]);
    }

    protected function getValidatorInstance()
    {
        $v = parent::getValidatorInstance();
        $v->sometimes("user", "required|uuid", function ($input) {
            return(Client::app()->app_name == "office");
        });

        return($v);
    }
}

<?php

namespace App\Http\Requests\Soundblock\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Client;

/**
 * @bodyParam phone_type string required
 * @bodyParam phone_number phone required
 * @bodyParam flag_primary boolean optional
 * @bodyParam user string optional The uuid of user
 */

class AddPhoneRequest extends FormRequest
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
            "phone_type" => "required|phone_type",
            "phone_number" => "required|string",//|phone:AUTO,US",
            "flag_primary" => "boolean",
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

<?php

namespace App\Http\Requests\User\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Client;

class UpdateBankAccountRequest extends FormRequest
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
            //
            "bank" => "required|uuid",
            "bank_name" => "string",
            "account_type" => "account_type",
            "account_number" => "digits_between:1,25",
            "routing_number" => "digits:9",
            "flag_primary" => "boolean"
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

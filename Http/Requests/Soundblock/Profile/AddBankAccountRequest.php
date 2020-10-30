<?php

namespace App\Http\Requests\Soundblock\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Client;

/**
 * @bodyParam bank_name string required
 * @bodyParam account_type string required
 * @bodyParam account_number numeric required
 * @bodyParam routing_number string required
 */

class AddBankAccountRequest extends FormRequest
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
            //
            "bank_name" => "required|string",
            "account_type" => "required|account_type",
            "account_number" => "required|numeric|digits_between:1,25",
            "routing_number" => "required|numeric|digits:9",
            "flag_primary" => "boolean"
        ];
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

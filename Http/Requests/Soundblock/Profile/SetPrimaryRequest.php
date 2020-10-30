<?php

namespace App\Http\Requests\Soundblock\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Client;

/**
 * @bodyParam type string required
 * @bodyParam bank string optional
 * @bodyParam paypal string optional
 * @bodyParam flag_primary boolean required
 */

class SetPrimaryRequest extends FormRequest
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
            "type"=> "required|string|in:bank,paypal",
            "bank" => "required_if:type,bank|uuid",
            "paypal" => "required_if:type,paypal|uuid",
            "flag_primary" => "required|boolean"
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

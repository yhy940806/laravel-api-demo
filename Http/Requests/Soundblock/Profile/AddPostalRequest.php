<?php

namespace App\Http\Requests\Soundblock\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Client;

/**
 * @bodyParam postal_type string required postal_type: "Home", "Office", "Billing", "Other"
 * @bodyParam postal_street string required string
 * @bodyParam postal_city string required string
 * @bodyParam postal_zipcode string required string
 * @bodyParam postal_country string required string
 */

class AddPostalRequest extends FormRequest
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
            "postal_type" => "required|postal_type",
            "postal_street" => "required|string",
            "postal_city" => "required|string",
            "postal_zipcode" => "required|postal_zipcode",
            "postal_country" => "required|string"
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

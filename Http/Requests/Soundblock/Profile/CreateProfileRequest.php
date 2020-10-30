<?php

namespace App\Http\Requests\Soundblock\Profile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam first_name string required
 * @bodyParam middle_name string
 * @bodyParam last_name string required
 * @bodyParam address array required
 * @bodyParam address.*.address_type string required
 * @bodyParam address.*.address string required
 * @bodyParam city string required
 * @bodyParam state string required
 * @bodyParam postal_code numeric required
 * @bodyParam country string required
 * @bodyParam phone array required
 * @bodyParam phone.*.phone_type string required
 * @bodyParam phone.*.phone_number numeric required
 *
 */
class CreateProfileRequest extends FormRequest
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
            "first_name" => "required|string",
            "middle_name" => "string",
            "last_name" => "required|string",
            "address.*" => "required|array|min:1",
            "address.*.address_type" => "required|string",
            "address.*.address" => "required|string",
            "city" => "required|string",
            "state" => "required|string",
            "postal_code" => "required|numeric",
            "country" => "required|string",
            "phone.*" => "required|array|min:1",
            "phone.*.phone_type" => "required|string",
            "phone.*.phone_number" => "required|numeric",
        ];
    }
}

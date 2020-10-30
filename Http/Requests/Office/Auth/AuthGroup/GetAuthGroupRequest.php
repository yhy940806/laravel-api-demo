<?php

namespace App\Http\Requests\Office\Auth\AuthGroup;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam group string required The uuid of group.
 */

class GetAuthGroupRequest extends FormRequest
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
            // "group" => "uuid",
            // "permissions.*" => "array|min:1",
            // "permissions.*.permission_uuid" => "required|uuid",
            // "per_page" => "integer|between:10,100"
            "group" => "required|uuid"
        ]);
    }
}

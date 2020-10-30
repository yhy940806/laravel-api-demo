<?php

namespace App\Http\Requests\Auth\Access;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam user string required user_uuid
 * @bodyParam group string required group_uuid
 * @bodyParam permissions array required
 * @bodyParam permissions.* string required
 */

class AddPermissionsToUserRequest extends FormRequest
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

    public function rules()
    {
        return([
            "user" => "required|uuid",
            "group" => "required|uuid",
            "permissions" => "required|array|min:1",
            "permissions.*" => "required|uuid",
        ]);
    }
}

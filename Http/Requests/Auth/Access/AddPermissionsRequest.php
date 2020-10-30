<?php

namespace App\Http\Requests\Auth\Access;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam permissions array optional
 * @bodyParam permissions.*.permission_name string required
 * @bodyParam permissions.*.permission_memo string required
 * @bodyParam permission_name string optional
 * @bodyParam permission_memo string optional
 */

class AddPermissionsRequest extends FormRequest
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
            "permissions" => "array|min:1",
            "permissions.*.permission_name" => "required|string",
            "permissions.*.permission_memo" => "required|string",
            "permission_name" => "string",
            "permission_memo" => "string",
        ]);
    }
}

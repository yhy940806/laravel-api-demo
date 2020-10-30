<?php

namespace App\Http\Requests\Auth\Access;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam permissions array optional
 * @bodyParam permissions.* string required
 * @bodyParam permission string optional
 */

class DeletePermissionsRequest extends FormRequest
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
            "permissions.*" => "required|uuid",
            "permission" => "uuid",
        ]);
    }
}

<?php

namespace App\Http\Requests\Office\Auth\AuthPermission;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuthPermissionInUserRequest extends FormRequest
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
            "group" => "required|uuid",
            "user" => "required|uuid",
            "permission_value" => "required|integer|in:0,1"
        ]);
    }
}

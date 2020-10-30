<?php

namespace App\Http\Requests\Soundblock\Project\Team;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
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
            "permissions" => "required|array",
            "permissions.*.permission_name" => "required|string",
            "permissions.*.permission_value" => "required|in:0,1"
        ]);
    }
}

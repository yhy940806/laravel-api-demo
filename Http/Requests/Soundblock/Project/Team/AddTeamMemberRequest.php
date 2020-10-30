<?php

namespace App\Http\Requests\Soundblock\Project\Team;

use Illuminate\Foundation\Http\FormRequest;

class AddTeamMemberRequest extends FormRequest
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
            "name" => "required|string",
            "user_auth_email" => "required|email",
            "user_role" => "required|string",
            "team" => "required|uuid",
            "permissions" => "required|array|min:1",
            "permissions.*.permission_name" => "required|string",
            "permissions.*.permission_value" => "required|integer|in:0,1"
        ]);
    }
}

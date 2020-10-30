<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SignUpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "name_first" => "required|string|max:999",
            "email" => ["required", "string", "email", "max:999", Rule::unique("users_emails", "user_auth_email")],
            "user_password" => "required|string|confirmed|max:99",
        ];
    }
}

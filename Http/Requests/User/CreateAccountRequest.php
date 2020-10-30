<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 *
 */
class CreateAccountRequest extends FormRequest
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
            "alias" => "required|string|unique:users_auth_aliases,user_alias",
            "email" => "required|email|unique:users_emails,user_auth_email",
            "password" => "required|min:6",
            "confirm_password" => "required|same:password"
        ]);
    }

}

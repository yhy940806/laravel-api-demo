<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SecurityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
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
            "old_password" => "sometimes|required_with:password|string",
            "password" => "sometimes|required|string|confirmed",
            "g2fa" => "sometimes|required|boolean",
            "force_reset" => "sometimes|required|boolean"
        ];
    }
}

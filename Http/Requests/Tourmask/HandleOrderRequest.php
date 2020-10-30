<?php

namespace App\Http\Requests\Tourmask;

use Illuminate\Foundation\Http\FormRequest;

class HandleOrderRequest extends FormRequest {
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
    public function rules() {
        return [
            "first_name"   => "required|string|min:1|max:999",
            "last_name"    => "required|string|min:1|max:999",
            "organization" => "required|string|min:1|max:999",
            "email"        => "required|string|email|min:1|max:999",
            "message"      => "required|string|min:1",
        ];
    }
}

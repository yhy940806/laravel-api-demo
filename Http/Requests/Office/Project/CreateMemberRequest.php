<?php

namespace App\Http\Requests\Office\Project;

use Illuminate\Foundation\Http\FormRequest;

class CreateMemberRequest extends FormRequest
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
    public function rules() {
        return [
            "name_first" => "required|string",
            "name_middle" => "sometimes|nullable|string",
            "name_last" => "sometimes|nullable|string",
            "email" => "required|email",
        ];
    }
}

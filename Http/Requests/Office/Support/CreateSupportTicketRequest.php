<?php

namespace App\Http\Requests\Office\Support;

use Illuminate\Foundation\Http\FormRequest;

class CreateSupportTicketRequest extends FormRequest
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
            "support" => "required|support.category",
            "title" => "required|string",
            "user_uuid" => "support.user",
            "message" => "sometimes|nullable|string",
            "file" => "sometimes|nullable|file"
        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_uuid.support.user' => 'User UUID is required in Office Project',
        ];
    }
}

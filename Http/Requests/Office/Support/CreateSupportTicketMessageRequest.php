<?php

namespace App\Http\Requests\Office\Support;

use Illuminate\Foundation\Http\FormRequest;

class CreateSupportTicketMessageRequest extends FormRequest
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
            "ticket" => "required|uuid",
            "user" => "uuid",
            "message_text" => "required|string",
            "flag_officeonly" => "required|boolean",
            "files" => "array|min:1",
            "files.*" => "file|mimes:png,jpg,bmp,tiff,jpeg,txt,pdf,doc,docx"
        ]);
    }
}

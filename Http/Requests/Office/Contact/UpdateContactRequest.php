<?php

namespace App\Http\Requests\Office\Contact;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
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
            "group" => "uuid|required_without:user",
            "user" => "uuid|required_without:group",
            "flag_read" => "boolean",
            "flag_archive" => "boolean",
            "flag_delete" => "boolean",
        ]);
    }
}

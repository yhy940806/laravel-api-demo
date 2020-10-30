<?php

namespace App\Http\Requests\Office\Project\Collection;

use Illuminate\Foundation\Http\FormRequest;

class ZipFileRequest extends FormRequest
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
        return [
            //
            "files" => "required|array|min:1",
            "files.*.file_uuid" => "required|uuid"
        ];
    }
}

<?php

namespace App\Http\Requests\Soundblock\Project;

use Illuminate\Foundation\Http\FormRequest;


/**
 * @bodyParam project string required
 * @bodyParam user string optional
 * @bodyParam project_notes string required
 * @bodyParam attachment_url url optional
 */

class CreateProjectNoteRequest extends FormRequest
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
            "project" => "required|uuid",
            "user" => "uuid",
            "project_notes" => "required|string",
            "files" => "array|min:1",
            "files.*" => "file|mimes:png,jpg,txt,pdf,bmp,tiff,jpeg,doc,docx"
        ]);
    }
}

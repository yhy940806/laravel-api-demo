<?php

namespace App\Http\Requests\Soundblock\Project;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmFilesRequest extends FormRequest
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
            "file_name" => "required|string",
            "collection_comment" => "required|string",
            "is_zip" => "required|boolean",
            "files" => "required|array|min:1",
            "files.*.org_file_sortby" => "string|required_if:is_zip,1",
            "files.*.file_title" => "required|string",
            "files.*.file_name" => "required|string",
            "files.*.file_track" => "integer|not_in:0",
            "files.*.file_path" => "string",
            "files.*.track.org_file_sortby" => "string",
            "files.*.track.file_uuid" => "uuid",
        ]);
    }
}

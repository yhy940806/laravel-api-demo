<?php

namespace App\Http\Requests\Soundblock\Collection;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam file_category string required music, video, merch, other
 * @bodyParam files array required The file list
 * @bodyParam files.*.file_uuid string required The uuid of file.
 * @bodyParam files.*.file_name string required The name of file
 * @bodyParam files.*.file_track string The uuid of music file, This will be required when the file category is "video".
 * @bodyParam files.*.file_title string the title of file
 * @bodyParam collection_comment string required
 * @bodyParam collection string required
 */

class EditFilesInCollectionRequest extends FormRequest
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
            "file_category" => "required|string",
            "files" => "required|array|min:1",
            "files.*.file_uuid" => "required|uuid",
            "files.*.file_name" => "required|string",
            "files.*.file_title" => "required|string",
            "files.*.meta.track_uuid" => "uuid",
            "files.*.meta.file_track" => "integer|min:1",
            "collection_comment" => "required|string",
            "project" => "required|uuid"
        ]);
    }
}

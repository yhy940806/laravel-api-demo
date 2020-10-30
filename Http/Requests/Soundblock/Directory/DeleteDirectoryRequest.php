<?php

namespace App\Http\Requests\Soundblock\Directory;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam collection string required The uuid of collection.
 * @bodyParam collection_comment string required The comment of collection.
 * @bodyParam file_category string required
 * @bodyParam directory_path string required The uuid of directory.
 * @bodyParam directory_name string required
 */


class DeleteDirectoryRequest extends FormRequest
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
            "project" => "required|uuid",
            "collection_comment" => "required|string",
            "file_category" => "required|string",
            "directory" => "required|uuid"
            // "directory_path" => "required|string",
            // "directory_name" => "required|string",
        ];
    }
}

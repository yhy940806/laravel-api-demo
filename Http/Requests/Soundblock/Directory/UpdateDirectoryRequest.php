<?php

namespace App\Http\Requests\Soundblock\Directory;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam file_category string required Music, Video, Merch, Other
 * @bodyParam collection string required The uuid of collection
 * @bodyParam collection_comment string required
 * @bodyParam directory_path string required
 * @bodyParam directory_name string required
 * @bodyParam new_directory_name string required
 */

 class UpdateDirectoryRequest extends FormRequest
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
            "project" => "required|uuid",
            "collection_comment" => "required|string",
            "directory_sortby" => "required|string",
            "new_directory_name" => "required|string|different:directory_name"
        ]);
    }
}

<?php

namespace App\Http\Requests\Soundblock\Collection\File;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam file_category string required
 * @bodyParam collection string required
 * @bodyParam collection_comment string required
 * @bodyParam files array required
 * @bodyParam files.*.file_uuid string required
 */

class RestoreFileRequest extends FormRequest
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
            "collection" => "required|uuid",
            "collection_comment" => "required|string",
            "files" => "required|array|min:1",
            "files.*.file_uuid" => "required|uuid"
        ]);
    }
}

<?php

namespace App\Http\Requests\Soundblock\Collection;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam file_category string required
 * @bodyParam collection string required
 * @bodyParam collection_comment string required
 * @bodyParam files array required
 * @bodyParam files.*.file_track integer required
 * @bodyParam files.*.file_uuid string required
 */

class OrganizeMusicRequest extends FormRequest
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
            "file_category" => "required|file_category",
            "collection" => "required|uuid",
            "collection_comment" => "required|string",
            "files" => "required|array|min:1",
            "files.*.file_track" => "required|integer|min:1",
            "files.*.file_uuid" => "required|uuid",
        ]);
    }
}

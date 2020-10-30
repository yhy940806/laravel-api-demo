<?php

namespace App\Http\Requests\Soundblock\Collection;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam project string required
 * @bodyParam file_category integer required
 * @bodyParam file file required
 * @bodyParam collection_comment string required
 * @bodyParam file file required
 * @bodyParam file_path string required
 */

class AddFileToCollectionRequest extends FormRequest
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
            "file" => "required|file",
        ]);
    }
}

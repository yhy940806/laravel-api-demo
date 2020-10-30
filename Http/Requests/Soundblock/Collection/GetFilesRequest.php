<?php

namespace App\Http\Requests\Soundblock\Collection;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam collection string required
 * @bodyParam file_path string required
 * @bodyParam collection string required
 *
 */

class GetFilesRequest extends FormRequest
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
            "file_path" => "required|string",
        ]);
    }
}

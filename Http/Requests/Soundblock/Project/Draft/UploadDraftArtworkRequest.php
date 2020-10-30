<?php

namespace App\Http\Requests\Soundblock\Project\Draft;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam service string required
 * @bodyParam draft string nullable
 * @bodyParam project_avatar file required mimetype png
 */

class UploadDraftArtworkRequest extends FormRequest
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
            "artwork" => "required|file|mimes:png,jpg",
        ]);
    }
}

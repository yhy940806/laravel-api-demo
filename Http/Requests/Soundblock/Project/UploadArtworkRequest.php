<?php

namespace App\Http\Requests\Soundblock\Project;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam project string required
 * @bodyParam project_avatar file required mimetype png
 */

class UploadArtworkRequest extends FormRequest
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
            "artwork" => "required|file|mimes:jpeg,jpg,png,bmp,tiff"
        ]);
    }
}

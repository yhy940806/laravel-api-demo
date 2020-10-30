<?php

namespace App\Http\Requests\Soundblock\Collection;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam project string required This is required to show all assets for specified project.
 */

class ShowCollectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "project" => "required|uuid"
        ];
    }
}

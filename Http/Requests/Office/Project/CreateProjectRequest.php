<?php

namespace App\Http\Requests\Office\Project;

use Illuminate\Foundation\Http\FormRequest;

class CreateProjectRequest extends FormRequest
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
            "service" => "required|uuid",
            "project_title" => "required|max:255",
            "project_date" => "required|date",
            "project_type" => "required|project_type",
            "project_artwork" => "required|file|mimes:png",
        ]);
    }
}

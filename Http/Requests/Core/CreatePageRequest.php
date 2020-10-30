<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;

class CreatePageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return (true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "app_name"          => "required|string|max:255",
            "page_url"          => "required|string|max:255",
            "page_title"        => "required|string|max:255",
            "page_keywords"     => "required|string|max:255",
            "page_url_params.*" => "required|string",
            "page_url_params"   => "required|array",
            "page_description"  => "required|string",
            "page_image"        => "sometimes|image"
        ];
    }
}

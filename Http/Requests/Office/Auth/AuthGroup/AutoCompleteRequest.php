<?php

namespace App\Http\Requests\Office\Auth\AuthGroup;

use App\Rules\Autocomplete\GroupAutocompleteRule;
use Illuminate\Foundation\Http\FormRequest;

class AutoCompleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            "name" => ["required_without:name", "string"],
            "memo" => "required_without:name|string",
            "select_fields" => ["sometimes", "string", new GroupAutocompleteRule()]
        ];
    }
}

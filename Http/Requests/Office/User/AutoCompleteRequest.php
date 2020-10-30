<?php

namespace App\Http\Requests\Office\User;

use App\Rules\Autocomplete\{UserAutocompleteRelationsRule, UserAutocompleteRule};
use Illuminate\Foundation\Http\FormRequest;

class AutoCompleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return(true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return([
            "user" => "required|string",
            "select_fields" => ["sometimes", "string", new UserAutocompleteRule()],
            "select_relations" => ["sometimes", "string", new UserAutocompleteRelationsRule($this->all())]
        ]);
    }
}

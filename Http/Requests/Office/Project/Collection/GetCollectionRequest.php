<?php

namespace App\Http\Requests\Office\Project\Collection;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam collection string required
 */

class GetCollectionRequest extends FormRequest
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
            //
            "collection" => "required|uuid"
        ]);
    }
}

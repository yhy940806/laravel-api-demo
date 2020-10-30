<?php

namespace App\Http\Requests\Office\Service;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam service string required
 */

class GetServiceNoteRequest extends FormRequest
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
            "service" => "required|uuid"
        ]);
    }
}

<?php

namespace App\Http\Requests\Office\Service;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam service string required
 * @bodyParam service_notes string required
 * @bodyParam user string required
 */

class CreateServiceNoteRequest extends FormRequest
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
            "service" => "required|uuid",
            "service_notes" => "required|string",
            "files" => "array|min:1",
            "files.*" => "file|mimes:png,jpg,tiff,bmp,pdf,doc,docx,txt|required"
            // "user" => "uuid"
        ]);
    }
}

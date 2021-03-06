<?php

namespace App\Http\Requests\Soundblock\Service;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam user string optional
 * @bodyParam service_name string required
 */

class CreateServiceRequest extends FormRequest
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
        return ([
            "user" => "uuid",
            "service_name" => "required|string"
        ]);
    }
}

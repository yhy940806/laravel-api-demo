<?php

namespace App\Http\Requests\Common\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePlanRequest extends FormRequest
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
            "service_name"              => ["required", "string"],
            "type"                      => ["required", "string", Rule::in(["Smart", "Simple"])],
            "payment_id"                => ["required", "string", "regex:/pm_.*/"]
        ]);
    }
}

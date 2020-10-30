<?php

namespace App\Http\Requests\Common\Service;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
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
    public function rules()
    {
        return [
            "type" => ["required", "string", Rule::in(["Smart", "Simple"])],
            "payment_id" => ["required", "string", "regex:/pm_.*/"]
        ];
    }
}

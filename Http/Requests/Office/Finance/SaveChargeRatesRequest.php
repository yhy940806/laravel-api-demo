<?php

namespace App\Http\Requests\Office\Finance;

use App\Rules\Office\Finance\RatesRule;
use Illuminate\Foundation\Http\FormRequest;

class SaveChargeRatesRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            "rates" => ["required", "array", new RatesRule()],
            "rates.*" => ["required", "numeric"]
        ];
    }
}

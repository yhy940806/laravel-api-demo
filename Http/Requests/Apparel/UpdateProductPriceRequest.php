<?php

namespace App\Http\Requests\Apparel;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductPriceRequest extends FormRequest
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
            "product_price" => "required|numeric",
            "range_min"     => "required|numeric",
            "range_max"     => "required|numeric"
        ];
    }
}

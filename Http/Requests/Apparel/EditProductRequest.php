<?php

namespace App\Http\Requests\Apparel;

use Illuminate\Foundation\Http\FormRequest;

class EditProductRequest extends FormRequest
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
            "product_name"             => "sometimes|string|max:255",
            "product_weight"           => "sometimes|numeric",
            "product_description"      => "sometimes|string|max:255",
            "product_meta_keywords"    => "sometimes|string|max:255",
            "product_meta_description" => "sometimes|string|max:255",
        ];
    }
}

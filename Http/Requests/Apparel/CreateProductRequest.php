<?php

namespace App\Http\Requests\Apparel;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
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
            "product.ascolour_id"              => "required|integer",
            "product.product_name"             => "required|string|max:255",
            "product.product_description"      => "required|string|max:255",
            "product.product_price"            => "required|numeric",
            "product.product_meta_keywords"    => "sometimes|string|max:255",
            "product.product_meta_description" => "sometimes|string",
            "product.product_weight"           => "required|numeric",
            "price.product_price"              => "required|numeric",
            "price.range_min"                  => "required|numeric",
            "price.range_max"                  => "required|numeric",
            "sizes.*"                          => "sometimes|string|max:255",
            "attribute.attribute_uuid"         => "required|string|max:255",
            "color.color_name"                 => "required|string|max:255",
            "color.color_hash"                 => "required|string|max:255"
        ];
    }
}

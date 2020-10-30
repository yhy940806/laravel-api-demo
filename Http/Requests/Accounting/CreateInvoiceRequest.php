<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class CreateInvoiceRequest extends FormRequest
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
            //
            "service"                       => ["required_without:user", "string"],
            "user"                          => ["required_without:service", "string"],
            "charge_type"                   => ["required", "string", "charge_type"],
            "invoice_type"                  => ["required", "string"],
            "line_items"                    => ["required", "array", "min:1"],
            "line_items.*.name"             => ["required", "string"],
            "line_items.*.cost"             => ["required", "regex:/^\d+(\.\d{1,2})?$/"],
            "line_items.*.quantity"         => ["required", "numeric", "min:0", "not_in:0"],
            "line_items.*.transaction_type" => ["required", "transaction_type"],
            "line_items.*.discount"         => ["required", "integer", "between:0,100"],
            "coupon"                        => ["string"],
            "discount"                      => ["required", "integer", "between:0,100"]
        ]);
    }
}

<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class GetInvoicesRequest extends FormRequest
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
        return [
            //
            "per_page"  => ["required", "integer", "between:10,100"],
            "sort_app"  => ["string", "in:asc,desc"],
            "sort_invoice_type"  => ["string", "in:asc,desc"],
        ];
    }
}

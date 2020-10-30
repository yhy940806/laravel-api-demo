<?php

namespace App\Http\Requests\Office\Support;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam sort_app string optional asc,desc
 * @queryParam sort_support_category string optional asc,desc
 * @queryParam sort_flag_status string optional asc,desc
 * @queryParam flag_status string optional open, closed, awating user, etc
 * @queryParam per_page integer optional 10-100
 */

class GetAllSupportRequest extends FormRequest
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
            "app" => "uuid",
            "flag_status" => "string|support.flag_status",
            "per_page" => "integer|between:10,100",
            "sort_app" => "string|in:asc,desc",
            "sort_flag_status" => "string|in:asc,desc",
            "sort_support_category" => "string|in:asc,desc",
            "support_category" => "string|support.category",
            "user" => "sometimes|required|uuid",
            "group" => "sometimes|required|uuid"
        ]);
    }
}

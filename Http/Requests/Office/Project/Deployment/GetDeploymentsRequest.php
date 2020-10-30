<?php

namespace App\Http\Requests\Office\Project\Deployment;

use Illuminate\Foundation\Http\FormRequest;

class GetDeploymentsRequest extends FormRequest
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
            "sort_platform" => "string|sort",
            "sort_deployment_status" => "string|sort",
            "sort_stamp_updated" => "string|sort",
            "per_page" => "integer|between:10,100"
        ]);
    }
}

<?php

namespace App\Http\Requests\Office\Project\Deployment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam deployments array required
 * @bodyParam deployments.*.platform string required
 * @bodyParam deployments.*.collection string required
 */

class CreateDeploymentsRequest extends FormRequest
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
            "deployments" => "required|array|min:1",
            "deployments.*.collection" => "required|uuid",
            "deployments.*.platform" => "required|uuid",
        ]);
    }
}

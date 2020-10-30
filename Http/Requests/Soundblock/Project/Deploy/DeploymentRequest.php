<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam deployment string required The uuid of deployment.
 * @bodyParam platform string required The uuid of platform.
 * @bodyParam project string required The uuid of project.
 */

class DeploymentRequest extends FormRequest
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
        return [
            "deployment" => "required|uuid",
            "platform" => "required|uuid",
            "project" => "required|uuid",
            "deployment_status" => "required|deployment_status"
        ];
    }
}

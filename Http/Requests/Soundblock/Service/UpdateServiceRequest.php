<?php

namespace App\Http\Requests\Soundblock\Service;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam service_name string required
 * @bodyParam service_uuid string required
 */

 class UpdateServiceRequest extends FormRequest
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
            "service_plan_name" => "required|string",
            "service" => "required|uuid"
         ]);
     }
 }

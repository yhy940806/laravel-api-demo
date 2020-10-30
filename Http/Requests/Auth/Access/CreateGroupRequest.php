<?php

namespace App\Http\Requests\Auth\Access;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam app string required
 * @bodyParam group_name string required
 * @bodyParam group_memo string required
 */

class CreateGroupRequest extends FormRequest
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
            "group_name" => "required|string",
            "group_memo" => "required|string"
         ]);
     }
}

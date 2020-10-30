<?php

namespace App\Http\Requests\Auth\Access;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam group string required
 * @bodyParam users array required
 * @bodyParam users.* uuid required
 */

class DeleteUsersInGroupRequest extends FormRequest
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
            "group" => "required|uuid",
            "users" => "required|array",
            "users.*" => "required|uuid",
        ]);
    }
}

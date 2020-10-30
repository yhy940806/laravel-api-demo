<?php

namespace App\Http\Requests\Auth\Access;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam app string reqired
 * @bodyParam group string reqired
 * @bodyParam users array reqired
 * @bodyParam users.* string reqired the uuid of user.
 */
class AddUserRequest extends FormRequest
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
           "users.*" => "required|uuid"
        ]);
    }

}

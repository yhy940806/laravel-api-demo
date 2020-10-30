<?php

namespace App\Http\Requests\Office\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam user string optional The uuid of the user.
 */

class UserRequest extends FormRequest
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
            "user" => "uuid",
            "per_page" => "integer|required_without:user"
        ]);
    }

}

<?php

namespace App\Http\Requests\Soundblock\User;

use Illuminate\Foundation\Http\FormRequest;

class AddOriginTeamMemberRequest extends FormRequest
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
            "users" => "required|array|min:1|sum",
            "project" => "required|uuid",
            "users.*.name" => "required|string",
            "users.*.email" => "required|string",
            "users.*.role" => "required|string",
            "users.*.payout"=> "required|integer|max:100"
        ];
    }
}

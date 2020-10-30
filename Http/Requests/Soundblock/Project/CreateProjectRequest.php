<?php

namespace App\Http\Requests\Soundblock\Project;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam project_title string required the title of project.
 * @bodyParam project_type string required the type of project.(Album, EP, Solo)
 * @bodyParam project_date date required (2020-01-01).
 * @bodyParam files file required mime zip
 * @bodyParam project_type string required Album, Ep, etc
 * @bodyParam members array required
 * @bodyParam members.*.user_name string required
 * @bodyParam members.*.user_auth_email email required
 * @bodyParam members.*.role string required
 * @bodyParam members.*.payout string required
 * @bodyParam contract_payment_message string required
 * @bodyParam contract_name string required
 * @bodyParam contract_email string required
 * @bodyParam contract_phone numeric required
 */

class CreateProjectRequest extends FormRequest
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
            "service" => "required|uuid",
            "project_title" => "required|max:255",
            "project_date" => "required|date",
            "artwork" => "required|string",
            "project_type" => "required|project_type",
            // "genre_uuids" => "required|array",
            // "genre_uuids.*" => "required|uuid",
            // "mood_uuids" => "required|array",
            // "mood_uuids.*" => "required|uuid",
            // "theme_uuids" => "required|array",
            // "theme_uuids.*" => "required|uuid"
        ];
    }
}

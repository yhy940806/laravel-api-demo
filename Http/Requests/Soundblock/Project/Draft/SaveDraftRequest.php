<?php

namespace App\Http\Requests\Soundblock\Project\Draft;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam service string required
 * @bodyParam step string required
 * @bodyParam draft string required
 * @bodyParam project_title string required
 * @bodyParam project_type string required
 * @bodyParam project_date date required
 * @bodyParam project_avatar url required
 * @bodyParam files file optional mimetype zip
 * @bodyParam tracks array optional
 * @bodyParam tracks.*.track_number integer required
 * @bodyParam tracks.*.file string required
 * @bodyParam members array
 * @bodyParam members.*.user_name string nullable
 * @bodyParam members.*.user_auth_email email nullable
 * @bodyParam members.*.role string nullable
 * @bodyParam members.*.payout integer nullable
 * @bodyParam contract_payment_message string nullable
 * @bodyParam contract_name string nullable
 * @bodyParam contract_email string nullable
 * @bodyParam contract_phone string nullable
 */

class SaveDraftRequest extends FormRequest
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
            //
            "service" => "required|uuid",
            "step" => "required|integer|min:1|max:3",
            "draft" => "uuid|nullable",
            "project_title" => "string|max:255|nullable",
            "project_type" => "project_type|nullable",
            "project_date" => "date|nullable",
            "artwork_url" => "url",
        ]);
    }
}

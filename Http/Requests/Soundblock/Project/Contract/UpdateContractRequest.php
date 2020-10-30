<?php

namespace App\Http\Requests\Soundblock\Project\Contract;

use App\Rules\Soundblock\Contract\PayoutRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            "members" => ["required", "array", "min:1", new PayoutRule($this->all())],
            "members.*.email" => "required|email",
            "members.*.name" => "required|string",
            "members.*.payout" => ["required", "numeric", "max:100", "min:0", "not_in:0"],
            "members.*.role" => "required|string",
        ];
    }
}

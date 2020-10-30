<?php

namespace App\Http\Requests\User\Profile;

use App\Rules\Office\OfficeRule;
use Client;
use Illuminate\Foundation\Http\FormRequest;

class GetEmailsRequest extends FormRequest
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
            "per_page" => "required|integer|between:5,50"
        ]);
    }

    protected function getValidatorInstance()
    {
        $v = parent::getValidatorInstance();
        $v->sometimes("user", "required|uuid", function ($input) {
            return(Client::app()->app_name == "office");
        });

        return($v);
    }
}

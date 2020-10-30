<?php

namespace App\Rules\Autocomplete;

use App\Helpers\Client;
use Illuminate\Contracts\Validation\Rule;

class GroupAutocompleteRule implements Rule {
    private string $failedFields = "";

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value) {
        $arrFields = collect(explode(",", $value));
        $appName = Client::app()->app_name;
        $allowedFields = config("constant.autocomplete.groups.allowed_fields.{$appName}");

        if (is_string($allowedFields) && $allowedFields == "*") {
            return true;
        }

        $invalidFields = $arrFields->diff($allowedFields);

        if ($invalidFields->isEmpty()) {
            return true;
        }

        $this->failedFields = $invalidFields->implode(", ");

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {
        if ($this->failedFields !== "") {
            return "The {$this->failedFields} fields are not supported for this request";
        }

        return 'Invalid Fields';
    }
}

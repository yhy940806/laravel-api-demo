<?php

namespace App\Rules\Autocomplete;

use App\Helpers\Client;
use Illuminate\Contracts\Validation\Rule;

class UserAutocompleteRelationsRule implements Rule {
    /**
     * Request Fields
     *
     * @var array
     */
    private array $arrRequest;

    private ?string $errorMessage = null;

    /**
     * Create a new rule instance.
     *
     * @param array $arrRequest
     */
    public function __construct(array $arrRequest) {
        $this->arrRequest = $arrRequest;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value) {
        $arrRelations = collect(explode(",", $value));
        $appName = Client::app()->app_name;
        $allowedFields = config("constant.autocomplete.users.allowed_relations.{$appName}");

        if ($allowedFields === "*") {
            return true;
        }

        $arrInvalidRelations = $arrRelations->diff(array_keys($allowedFields));

        if ($arrInvalidRelations->isNotEmpty()) {
            $this->errorMessage = "The {$arrInvalidRelations->implode(', ')} relations are not supported by this request.";
            return false;
        }

        foreach ($arrRelations as $strRelation) {
            if (!isset($this->arrRequest[$strRelation . "_fields"])) {
                continue;
            }

            $arrRequestFields = collect(explode(",", $this->arrRequest[$strRelation . "_fields"]));
            $allowedFields = $allowedFields[$strRelation];

            if (is_string($allowedFields)) {
                if ($allowedFields == "*") {
                    $fields = config("constant.autocomplete.users.fields_alias.relations");
                    $allowedFields = array_keys($fields[$strRelation]);
                } else {
                    $allowedFields = [$allowedFields];
                }

            }

            $objInvalidFields = $arrRequestFields->diff($allowedFields);

            if ($objInvalidFields->isNotEmpty()) {
                $this->errorMessage = "The {$objInvalidFields->implode(', ')} fields are not supported by {$strRelation} relation.";

                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {
        if (is_null($this->errorMessage)) {
            return 'The validation error message.';
        }

        return $this->errorMessage;
    }
}

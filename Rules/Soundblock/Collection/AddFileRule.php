<?php

namespace App\Rules\Soundblock\Collection;

use Illuminate\Contracts\Validation\Rule;

class AddFileRule implements Rule {

    /**
     * Request Fields
     *
     * @var array
     */
    private array $arrRequests;

    private ?string $errorMessage = null;

    /**
     * Create a new rule instance.
     * @param array $arrRequests
     * @return void
     */
    public function __construct(array $arrRequests) {
        $this->arrRequests = $arrRequests;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value) {

        $file = $this->arrRequests["file"];

        if ($file->getMimeType() == "zip") {
            return (true);
        } else if (is_null($value)) {
            $this->errorMessage = sprintf("The (%s) field is required.", $attribute);
            return (false);
        } else {
            return (true);
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {
        if (is_null($this->errorMessage)) {
            return ("The validation error message.");
        } else {
            return ($this->errorMessage);
        }

    }
}

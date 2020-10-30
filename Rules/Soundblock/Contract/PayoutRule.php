<?php

namespace App\Rules\Soundblock\Contract;

use Illuminate\Contracts\Validation\Rule;

class PayoutRule implements Rule {
    /**
     * @var array
     */
    private array $arrRequest;

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
        return collect($this->arrRequest["members"])->sum("payout") == 100;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {
        return "The sum of user's payout must be equal 100.";
    }
}

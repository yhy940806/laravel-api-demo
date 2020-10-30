<?php

namespace App\Rules\Office\Finance;

use Illuminate\Support\Arr;
use Illuminate\Contracts\Validation\Rule;

class RatesRule implements Rule {
    const ALLOWED_KEYS = [
        "contract", "user", "upload", "download",
    ];

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value) {
        if (count($value) !== count(self::ALLOWED_KEYS)) {
            return false;
        }

        return Arr::has($value, self::ALLOWED_KEYS);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {
        return "Rate's array is not valid.";
    }
}

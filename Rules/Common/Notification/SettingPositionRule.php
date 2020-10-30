<?php

namespace App\Rules\Common\Notification;

use Client;
use Illuminate\Contracts\Validation\Rule;

class SettingPositionRule implements Rule {
    /** @var string */
    protected string $errMessage;

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

        if (!is_string($value))
            return (false);
        $platform = strtolower(Client::platform()) == "web" ? "web" : "mobile";
        $arrPosition = config("constant.notification.position");

        foreach ($arrPosition[$platform] as $position) {
            if ($position == strtolower($value))
                return (true);
        }
        $this->errMessage = sprintf("Position (%s) is n't supported on %s", $value, $platform);

        return (false);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {
        if (is_null($this->errMessage))
            return ("Position is invalid.");
        return ($this->errMessage);
    }
}

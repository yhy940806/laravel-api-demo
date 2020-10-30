<?php

namespace App\Exceptions;

use Exception;

class PendingUserExcption extends Exception
{
    //
    protected $pendingUserEmail;

    public function __construct($pendingUserEmail, $message = "", $code = 0, Exception $previous = null)
    {
        $this->pendingUserEmail = $pendingUserEmail;

        $message = "Pending User Excption";
        $code = 400;
        parent::__construct($message, $code, $previous);
    }

    public static function emailDuplication($pendingUserEmail, Exception $previous = null)
    {
        return(new static($pendingUserEmail, sprintf("Email %s duplicated. Please try again.", 400, $previous)));
    }
}

<?php

namespace App\Exceptions;

use Exception;

class IllegalRequestException extends Exception
{
    //
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        $message = "This is illegal request.";
        $code = 400;
        parent::__construct($message, $code, $previous);
    }

    public function setCustomMessage($message, $code =400, Exception $previous = null)
    {
        return(new static($message, $code, $previous));
    }
}

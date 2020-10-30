<?php

namespace App\Exceptions;

use Exception;

class UnzipException extends Exception
{
    //
    public function __construct($code = 0, $message = "",  Exception $previous = null)
    {
        $code = 417;
        $message = "Extracting files and directories Exception.";
        parent::__construct($message, $code, $previous);
    }

    public function report()
    {
        \Log::debug("Extracting files and directories Exception.");
    }
}

<?php


namespace App\Exceptions;

use App\Contracts\Exceptions\ExceptionContract;
use Throwable;

class LedgerMicroserviceException extends DisasterExceptions implements ExceptionContract {
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

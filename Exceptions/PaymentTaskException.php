<?php

namespace App\Exceptions;

use Throwable;
use App\Contracts\Exceptions\ExceptionContract;

class PaymentTaskException extends DisasterExceptions implements ExceptionContract{
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

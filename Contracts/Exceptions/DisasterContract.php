<?php

namespace App\Contracts\Exceptions;

interface DisasterContract {
    public function handleDisaster(ExceptionContract $exception);
}

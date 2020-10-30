<?php

namespace App\Contracts\Exceptions;

use App\Models\User;

interface ExceptionContract extends \Throwable {
    public function getDetails() : array;
    public function getUser() : ?User;

    public function isHttp() : bool;
    public function isCommand() : bool;
}

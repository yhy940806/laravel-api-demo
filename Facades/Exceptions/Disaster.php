<?php


namespace App\Facades\Exceptions;

use App\Contracts\Exceptions\ExceptionContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static handleDisaster(ExceptionContract $exception)
 */
class Disaster extends Facade {
    protected static function getFacadeAccessor() {
        return 'disaster';
    }
}

<?php

namespace App\Exceptions;

use Throwable;
use Exception;
use App\Traits\Response;
use App\Facades\Exceptions\Disaster;
use Illuminate\Validation\ValidationException;
use App\Contracts\Exceptions\ExceptionContract;
use Dingo\Api\Exception\Handler as DingoHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiHandler extends DingoHandler {
    use Response;

    /**
     * @param Throwable|Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function handle($exception) {
        if ($exception instanceof ValidationException) {
            return parent::handle($exception);
        }

        $response = [
            "error" => "Sorry, can not excute your request",
        ];

        if (config("api.debug")) {
            $response["exception"] = get_class($exception);
            $response["trace"] = $exception->getTrace();
        }

        $message = $exception->getMessage();
        if ($exception->getCode() >= 400 && $exception->getCode() <= 500) {
            $statusCode = $exception->getCode();
        } else {
            $statusCode = 400;
        }

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $this->getExceptionStatusCode($exception);
        } else if ($exception instanceof ExceptionContract) {
            Disaster::handleDisaster($exception);
        }

        return ($this->failure($response, $message, $statusCode));
    }
}

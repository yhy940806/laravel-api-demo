<?php

namespace App\Exceptions;

use Throwable;
use Exception;
use App\Facades\Exceptions\Disaster;
use App\Contracts\Exceptions\ExceptionContract;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler {
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    private string $disasterExceptions = ExceptionContract::class;

    /**
     * Report or log an exception.
     *
     * @param \Throwable $e
     * @return void
     * @throws Exception
     */
    public function report(Throwable $e) {
        if ($e instanceof $this->disasterExceptions) {
            /** @var ExceptionContract $e */
            Disaster::handleDisaster($e);
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function render($request, Throwable $exception) {
        if (!empty($exception)) {
            $response = [
                "error" => "Sorry, can not excute your request",
            ];

            if (config("api.debug")) {
                $response["exception"] = get_class($exception);
                $response["message"] = $exception->getMessage();
                $response["trace"] = $exception->getTrace();
            }

            //default status
            $status = 400;

            return response()->json($response, $status);
        }

        return (parent::render($request, $exception));
    }

    /**
     * Get the status code from the exception.
     *
     * @param \Exception $exception
     *
     * @return int
     */
    protected function getStatusCode(Exception $exception) {
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return $exception->status;
        }

        return $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
    }
}

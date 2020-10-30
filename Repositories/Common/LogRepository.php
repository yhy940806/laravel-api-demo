<?php

namespace App\Repositories\Common;

use Carbon\Carbon;
use App\Helpers\Util;
use App\Models\Common\{Log, LogError};
use App\Contracts\Exceptions\ExceptionContract;

class LogRepository {

    protected Log $model;
    /**
     * @var LogErrorRepository
     */
    private LogErrorRepository $logErrorRepository;

    /**
     * LogRepository constructor.
     * @param Log $logError
     * @param LogErrorRepository $logErrorRepository
     */
    public function __construct(Log $logError, LogErrorRepository $logErrorRepository) {
        $this->model = $logError;
        $this->logErrorRepository = $logErrorRepository;
    }

    /**
     * @param Carbon $period
     * @param ExceptionContract $exceptionContract
     * @return bool
     */
    public function canSkipLog(Carbon $period, ExceptionContract $exceptionContract): bool {
        return $this->logErrorRepository->checkExceptionExistInPeriod($period, $exceptionContract);
    }

    /**
     * @param ExceptionContract $exception
     * @return Log
     * @throws \Exception
     */
    public function createLog(ExceptionContract $exception): Log {
        $arrExDetails = $exception->getDetails();

        /** @var Log $log */
        $log = $this->model->create(["log_uuid" => Util::uuid()]);
        /** @var LogError $logError */
        $logError = $log->logError()->create([
            "row_uuid"          => Util::uuid(),
            "log_uuid"          => $log->log_uuid,
            "log_command"       => $exception->isCommand() ? $arrExDetails["command"] : null,
            "log_url"           => $exception->isHttp() ? $arrExDetails["endpoint"] : null,
            "log_method"        => $exception->isHttp() ? $arrExDetails["method"] : null,
            "log_request"       => $exception->isHttp() ? $arrExDetails["request"] : null,
            "exception_class"   => get_class($exception),
            "exception_message" => $arrExDetails["message"],
            "exception_trace"   => $arrExDetails["trace"],
            "exception_code"    => $arrExDetails["code"],
        ]);

        $objUser = $exception->getUser();

        if (!is_null($objUser)) {
            $logError->user()->associate($objUser);
            $logError->user_uuid = $objUser->user_uuid;
            $logError->save();
        }

        return $log;
    }
}

<?php

namespace App\Repositories\Common;

use Carbon\Carbon;
use App\Models\Common\LogError;
use App\Contracts\Exceptions\ExceptionContract;

class LogErrorRepository {
    /**
     * @var LogError
     */
    private LogError $model;

    public function __construct(LogError $logError) {
        $this->model = $logError;
    }

    /**
     * @param Carbon $period
     * @param ExceptionContract $exceptionContract
     * @return bool
     */
    public function checkExceptionExistInPeriod(Carbon $period, ExceptionContract $exceptionContract): bool {
        $exDetails = $exceptionContract->getDetails();

        return $this->model->where("stamp_created_at", ">", $period)
                           ->where("exception_class", get_class($exceptionContract))
                           ->where("exception_message", $exDetails["message"])->exists();
    }
}

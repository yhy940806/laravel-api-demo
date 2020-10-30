<?php

namespace App\Observers\Common;

use Util;
use App\Models\Common\QueueJob;

class QueueJobObserver {

    /**
     * @param QueueJob $objQueueJob
     * @return void
     */
    public function released(QueueJob $objQueueJob) {
        $objQueueJob->{QueueJob::STOP_AT} = Util::now();
        $objQueueJob->{QueueJob::STAMP_STOP} = microtime(true);
        $objQueueJob->flag_status = "Succeeded";
        $objQueueJob->job_seconds = round($objQueueJob->{QueueJob::STAMP_STOP} - $objQueueJob->{QueueJob::STAMP_START});

        $objQueueJob->save();
    }

    /**
     * @param QueueJob $objQueueJob
     * @return void
     */
    public function failed(QueueJob $objQueueJob) {
        $objQueueJob->flag_status = "Failed";
        $objQueueJob->save();
    }
}

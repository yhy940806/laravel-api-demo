<?php

namespace App\Repositories\Common;

use Util;
use App\Repositories\BaseRepository;
use App\Models\{User, Common\QueueJob};
use Illuminate\Support\Collection as SupportCollection;

class QueueJobRepository extends BaseRepository {
    /**
     * @param QueueJob $queueJob
     * @return void
     */
    public function __construct(QueueJob $queueJob) {
        $this->model = $queueJob;
    }

    public function find($id, bool $bnFailure = false) {
        if ($bnFailure) {
            if (is_int($id)) {
                return ($this->model->lockForUpdate()->findOrFail($id));
            } else if (Util::is_uuid($id)) {
                return ($this->model->lockForUpdate()->where($this->model->uuid(), $id)->firstOrFail());
            }
        } else {
            if (is_int($id)) {
                return ($this->model->lockForUpdate()->find($id));
            } else if (Util::is_uuid($id)) {
                return ($this->model->lockForUpdate()->where($this->model->uuid(), $id)->first());
            }
        }
    }

    /**
     * @param int $job
     * @return QueueJob|null
     */
    public function findByJobId(int $job, bool $bnFailure = false): ?QueueJob {
        $query = $this->model->lockForUpdate()->where("queue_id", $job);
        if ($bnFailure) {
            return ($query->first());
        } else {
            return ($query->firstOrFail());
        }
    }

    /**
     * @param array $arrQueueJob
     * @return QueueJob
     * @throws \Exception
     */
    public function createModel(array $arrQueueJob) {
        $model = new QueueJob;
        $uuid = $this->model->uuid();
        if (!isset($arrQueueJob[$uuid])) {
            $arrQueueJob[$uuid] = Util::uuid();
        }
        $model->fill($arrQueueJob);
        $model->save();

        return ($model);
    }

    /**
     * @param int $intQueue
     * @return QueueJob
     */
    public function findByQueue(int $intQueue, ?bool $bnFailure = true) {
        if ($bnFailure) {
            return ($this->model->where("queue_id", $intQueue)->firstOrFail());
        } else {
            return ($this->model->where("queue_id", $intQueue)->first());
        }
    }

    /**
     * @param User|null $user
     * @return SupportCollection
     */
    public function findAllPending(?User $user = null): SupportCollection {
        $now = microtime(true);
        if ($user) {
            return ($user->jobs()->where("flag_status", "Pending")->whereRaw("$now - stamp_start < 7200")->get());
        } else {
            return ($this->model->where("flag_status", "Pending")->whereRaw("$now - stamp_start < 7200")->get());
        }

    }

    /**
     * @param User|null $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findAllRunning(?User $user = null) {
        $now = microtime(true);
        if ($user) {
            return ($user->jobs()->whereNotNull("queue_id")->where("flag_status", "Pending")
                         ->where(function ($query) use ($now) {
                             $query->whereRaw("$now - stamp_start <= 7200")
                                   ->orWhereRaw("$now -stamp_start >= 0");
                         })->get());
        } else {
            return ($this->model->whereNotNull("queue_id")->where("flag_status", "Pending")
                                ->where(function ($query) use ($now) {
                                    $query->whereRaw("$now - stamp_start <= 7200")
                                          ->orWhereRaw("$now -stamp_start >= 0");
                                })->get());
        }
    }

    /**
     * @param QueueJob|null $objQueueJob
     * @return array
     */
    public function findPendingJobType(QueueJob $objQueueJob = null): array {
        $now = microtime(true);
        $query = $this->model->whereNotNull("job_type")
                             ->where(function ($query) use ($now) {
                                 $query->whereRaw("$now - stamp_start <= 7200")
                                       ->orWhereRaw("$now - stamp_start >= 0");
                             })->where("flag_status", "Pending")->groupBy("job_type");
        if ($objQueueJob)
            $query = $query->where(QueueJob::STAMP_START, "<=", $objQueueJob->{QueueJob::STAMP_START});
        return ($query->get()->pluck("job_type")->toArray());
    }

    /**
     * @param QueueJob $objJob
     * @param string $strJobType
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findAllAhead(QueueJob $objJob, ?string $strJobType = null) {
        $now = microtime(true);
        $query = $this->model->where(QueueJob::STAMP_START, "<", $objJob->{QueueJob::STAMP_START})
                             ->where(function ($query) use ($now) {
                                 $query->whereRaw("$now - stamp_start <= 7200")
                                       ->orWhereRaw("$now - stamp_start >= 0");
                             })->where("flag_status", "Pending");
        if ($strJobType && $objJob->job_type) {
            $query = $query->where("job_type", $strJobType);
        }

        return ($query->get());
    }

    /**
     * @param string $strJobType
     * @return int
     */
    public function getCountPendingJob(string $strJobType): int {
        $now = microtime(true);
        return ($this->model->whereRaw("lower(flag_status) = (?)", Util::lowerLabel("Pending"))
                            ->whereRaw("lower(job_type) = (?)", Util::lowerLabel($strJobType))
                            ->where(function ($query) use ($now) {
                                $query->whereRaw("$now - stamp_start <= 7200")
                                      ->orWhereRaw("$now - stamp_start >= 0");
                            })
                            ->count());
    }

    /**
     * @param string $strJobType
     * @return int
     */
    public function getAvgTime(?string $strJobType = null): int {
        $query = $this->model->whereNotNull("queue_id")
                             ->whereRaw("lower(flag_status) = (?)", Util::lowerLabel("Succeeded"));
        if ($strJobType) {
            $query = $query->whereRaw("lower(job_type) = (?)", Util::lowerLabel($strJobType));
        }

        return (round($query->avg("job_seconds")));
    }

    public function getJobsForDownload() {
        return $this->model->where("flag_remove_file", true)->get();
    }
}

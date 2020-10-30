<?php

namespace App\Models\Common;

use App\Models\{Core\App, User};
use App\Services\AuthService;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Util;

class QueueJob extends Model
{
    //
    protected $table = "queue_jobs";

    protected $primaryKey = "job_id";

    protected string $uuid = "job_uuid";

    protected $hidden = [
        "job_id", "queue_id", "user_id", "user_uuid", "app_id", "app_uuid", "job_name", "job_script",
        "job_memo", "job_seconds", "stamp_start_at", "stamp_stop_at", "user"
    ];

    protected $observables = [
        "released", "failed"
    ];

    protected $guarded = [];

    protected $casts = [
        "job_json" => "array"
    ];

    protected $appends = [
        "job_detail"
    ];

    public $timestamps = false;

    const START_AT = "stamp_start_at";

    const STAMP_START = "stamp_start";

    const IDX_STAMP_START = "idx_stamp-start";

    const STOP_AT = "stamp_stop_at";

    const STAMP_STOP = "stamp_stop";

    const IDX_STAMP_STOP = "idx_stamp-stop";

    public function uuid()
    {
        return($this->uuid);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{static::START_AT} = Util::now();
            $model->{static::STAMP_START} = microtime(true);
        });
    }

    public function released()
    {
        $this->fireModelEvent("released", false);
    }

    public function failed()
    {
        $this->fireModelEvent("failed", false);
    }

    public function user()
    {
        return($this->belongsTo(User::class, "user_id", "user_id"));
    }

    public function app()
    {
        return($this->belongsTo(App::class, "app_id", "app_id"));
    }

    public function getJobDetailAttribute()
    {
        /** @var \App\Services\AuthService */
        $authService = resolve(AuthService::class);
        if (Auth::user() && $authService->checkOfficeUser(Auth::user())) {
            return([
                "job_name"          => $this->job_name,
                "job_memo"          => $this->job_memo,
                "job_script"        => $this->job_script,
                "user"              => $this->user->only("name", "user_uuid", "primary_email")
            ]);
        } else {
            return([]);
        }
    }
}

<?php

namespace App\Models\Common;

use App\Models\BaseModel;
use App\Models\User;

class LogError extends BaseModel
{
    protected $primaryKey = "row_id";

    protected $casts = [
        "exception_trace" => "array",
        "log_request" => "array",
    ];

    public function user() {
        return $this->belongsTo(User::class, "user_id");
    }
}

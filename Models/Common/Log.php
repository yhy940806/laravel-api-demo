<?php

namespace App\Models\Common;

use App\Models\BaseModel;

class Log extends BaseModel
{
    protected $table = "logs";

    protected $primaryKey = "log_id";

    protected $guarded = [];

    protected $hidden = [
        "log_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::UPDATED_AT, BaseModel::STAMP_UPDATED_BY, BaseModel::CREATED_AT, BaseModel::STAMP_CREATED_BY,
    ];

    protected string $uuid = "log_uuid";

    public function logError() {
        return $this->hasOne(LogError::class, "log_id");
    }
}

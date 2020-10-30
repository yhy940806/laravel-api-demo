<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class ServicePlan extends BaseModel
{
    protected $table = "soundblock_services_plans";

    protected $primaryKey = "plan_id";

    protected string $uuid = "plan_uuid";

    protected $guarded = [];

    protected $hidden = [
        "plan_id", "service_id", "ledger_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY
    ];

    public function service()
    {
        return($this->belongsTo(Service::class, "service_id", "service_id"));
    }

    public function scopeActive(Builder $query) : Builder {
        return $query->where("flag_active", true);
    }
}

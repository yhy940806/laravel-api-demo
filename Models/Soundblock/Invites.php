<?php

namespace App\Models\Soundblock;

use App\Helpers\Util;
use App\Models\BaseModel;
use Illuminate\Support\Str;

class Invites extends BaseModel
{
    protected $table = "soundblock_invites";

    protected $primaryKey = "invite_id";

    protected string $uuid = "invite_uuid";

    protected $guarded = [];

    protected $hidden = [
        "invite_id", "invite_hash", "flag_used", "table_name", "table_field", "table_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::UPDATED_AT, BaseModel::CREATED_AT,
        "pivot"
    ];

    protected static function boot() {
        parent::boot();

        static::creating(function ($model) {
            $model->invite_hash = Str::random(32);
        });
    }

    public function contracts()
    {
        return($this->belongsToMany(Contract::class, "soundblock_projects_contracts_users", "invite_id", "contract_id", "invite_id", "contract_id")
            ->whereNull("soundblock_projects_contracts_users." . BaseModel::STAMP_DELETED)
            ->withPivot("contract_uuid", "user_uuid", "user_payout", "contract_status")
            ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function invitable()
    {
        return $this->morphTo("invitable", "table_name", "table_id");
    }
}

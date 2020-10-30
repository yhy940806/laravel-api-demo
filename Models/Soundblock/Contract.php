<?php

namespace App\Models\Soundblock;

use App\Models\User;
use App\Models\BaseModel;

class Contract extends BaseModel
{
    protected $table = "soundblock_projects_contracts";

    protected $primaryKey = "contract_id";

    protected string $uuid = "contract_uuid";

    protected $guarded = [];

    protected $hidden = [
        "contract_id", "project_id", "service_id", "ledger_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT, "pivot" //
    ];

    const STAMP_BEGINS = "stamp_begins";
    const IDX_STAMP_BEGINS = "idx_stamp-begins";
    const STAMP_ENDS = "stamp_ends";
    const IDX_STAMP_ENDS = "idx_stamp-ends";

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->stamp_begins = time();
        });
    }

    public function project()
    {
        return($this->belongsTo(Project::class, "project_id", "project_id"));
    }

    public function users()
    {
        return($this->belongsToMany(User::class, "soundblock_projects_contracts_users", "contract_id", "user_id", "contract_id", "user_id")
                    ->whereNull("soundblock_projects_contracts_users." . BaseModel::STAMP_DELETED)
                    ->withPivot("contract_uuid", "user_uuid", "invite_id", "invite_uuid", "user_payout", "contract_status")
                    ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function invites()
    {
        return($this->morphMany(Invites::class, "invitable", "table_name", "table_id"));
    }

    public function contractInvites()
    {
        return($this->belongsToMany(Invites::class, "soundblock_projects_contracts_users", "contract_id", "invite_id", "contract_id", "invite_id")
                    ->whereNull("soundblock_projects_contracts_users." . BaseModel::STAMP_DELETED)
                    ->withPivot("invite_uuid", "contract_uuid", "user_id", "user_uuid", "user_payout", "contract_status")
                    ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function history() {
        return $this->hasMany(ContractHistory::class, "contract_id", "contract_id");
    }

    public function usersHistory() {
        return $this->hasMany(ContractUserHistory::class, "contract_id", "contract_id");
    }

    public function ledger() {
        return $this->belongsTo(Ledger::class, "ledger_id", "ledger_id");
    }

    public function service() {
        return $this->belongsTo(Service::class, "service_id", "service_id");
    }
}

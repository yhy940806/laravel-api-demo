<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;
use App\Models\Accounting\AccountingFailedPayments;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Illuminate\Database\Eloquent\Collection transactions
 * @property User user
 */
class Service extends BaseModel {
    //
    use SoftDeletes;

    protected $table = "soundblock_services";

    protected $primaryKey = "service_id";

    protected string $uuid = "service_uuid";

    protected $hidden = [
        "service_id", "user_id", "user", BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::STAMP_CREATED_BY, BaseModel::STAMP_UPDATED_BY,
        BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::STAMP_CREATED, BaseModel::STAMP_UPDATED,
    ];

    protected $guarded = [];

    protected $appends = ["service_holder"];

    public function user() {
        return ($this->belongsTo(User::class, "user_id", "user_id"));
    }

    public function projects() {
        return ($this->hasMany(Project::class, "service_id", "service_id"));
    }

    public function transactions() {
        return ($this->hasMany(ServiceTransaction::class, "service_id", "service_id"));
    }

    public function plans() {
        return ($this->hasMany(ServicePlan::class, "service_id", "service_id"));
    }

    public function drafts() {
        return ($this->hasMany(ProjectDraft::class, "service_id", "service_id"));
    }

    public function failedPayments() {
        return $this->hasMany(AccountingFailedPayments::class, "service_id", "service_id");
    }

    public function downloads() {
        if ($this->transactions->count() == 0) {
            return (0);
        } else {
            return ($this->transactions()->where("transaction_name", "download")->count());
        }

    }

    public function notes() {
        return ($this->hasMany(ServiceNote::class, "service_id", "service_id"));
    }

    public function scopeActive(Builder $builder) {
        return $builder->where("flag_status", "active");
    }

    public function getServiceHolderAttribute() {
        return($this->user->name);
    }

}

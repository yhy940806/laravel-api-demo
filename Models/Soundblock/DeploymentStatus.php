<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;

class DeploymentStatus extends BaseModel
{
    //
    protected $table = "soundblock_projects_deployments_status";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $guarded = [];

    protected $hidden = [
        "row_id", "deployment_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY
    ];

    public function deployment()
    {
        return($this->belongsTo(Deployment::class, "deployment_id", "deployment_id"));
    }
}

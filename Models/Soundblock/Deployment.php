<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;

class Deployment extends BaseModel
{
    //
    protected $table = "soundblock_projects_deployments";

    protected $primaryKey = "deployment_id";

    protected string $uuid = "deployment_uuid";

    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        "deployment_id", "project_id", "platform_id", "collection_id",
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
    ];

    public function platform()
    {
        return($this->belongsTo(Platform::class, "platform_id", "platform_id"));
    }

    public function project()
    {
        return($this->belongsTo("App\Models\Soundblock\Project", "project_id", "project_id"));
    }

    public function status()
    {
        return($this->hasOne(DeploymentStatus::class, "deployment_id", "deployment_id"));
    }

    public function collection()
    {
        return($this->hasOne(Collection::class, "collection_id", "collection_id"));
    }

    public function distributions()
    {
        // return($this->belongsToMany())
    }
}

<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;
use Storage;
use Util;

class Project extends BaseModel
{
    //
    protected $table = "soundblock_projects";

    protected $primaryKey = "project_id";

    protected string $uuid = "project_uuid";

    protected $guarded = [];

    protected $hidden = [
        "project_id", "service_id", BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY
    ];

    /**
     * The projects that belongs to the user
     */

    public function service()
    {
        return($this->belongsTo(Service::class, "service_id", "service_id"));
    }

    public function contracts()
    {
        return($this->hasMany(Contract::class, "project_id", "project_id"));
    }

    public function team()
    {
        return($this->hasOne(Team::class, "project_id", "project_id"));
    }

    public function deployments()
    {
        return($this->hasMany(Deployment::class, "project_id", "project_id"));
    }

    public function collections()
    {
        return($this->hasMany(Collection::class, "project_id", "project_id"));
    }

    public function notes()
    {
        return($this->hasMany(ProjectNote::class, "project_id", "project_id"));
    }

    public function getArtworkAttribute()
    {
        if (env("APP_ENV") == "local") {
            /** @var \Illuminate\Filesystem\FilesystemAdapter */
            $fileAdapter = Storage::disk("local");
        } else {
            /** @var \Illuminate\Filesystem\FilesystemAdapter */
            $fileAdapter = Storage::disk("s3-soundblock");
        }
        return($fileAdapter->exists(Util::artwork_path($this)) ? Util::relative_artwork_path($this) : config("constant.project_avatar"));
    }

    public function getDeploymentAttribute()
    {
        return($this->deployments()->orderBy("collection_id", "desc")->first());
    }
}

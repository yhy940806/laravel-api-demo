<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectDraft extends BaseModel
{
    use SoftDeletes;

    protected $primaryKey = "draft_id";

    protected string $uuid = "draft_uuid";

    protected $table = "soundblock_projects_drafts";

    protected $casts = [
        "draft_json" => "array"
    ];

    protected $hidden = [
        "draft_id", "service_id",
        BaseModel::CREATED_AT, BaseModel::STAMP_CREATED_BY, BaseModel::UPDATED_AT, BaseModel::STAMP_UPDATED_BY,
        BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY
    ];

    protected $guarded = [];

    public function service()
    {
        return($this->belongsTo(Service::class, "service_id", "service_id"));
    }

    public function setPropertiesAttribute($value)
    {
        $properties = [];
        foreach($value as $array_item)
        {
            if (!is_null($array_item["key"]))
            {
                $properties[] = $array_item;
            }
        }
        $this->attributes["properties"] = json_encode($properties);
    }
}

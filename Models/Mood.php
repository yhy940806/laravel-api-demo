<?php

namespace App\Models;


class Mood extends BaseModel {
    protected $table = "soundblock_projects_metadata_mood";

    protected $primaryKey = "row_id";

    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        "row_id", "row_uuid", "project_id", "mood_id", BaseModel::STAMP_CREATED_BY, BaseModel::STAMP_UPDATED_BY, BaseModel::STAMP_DELETED_BY,
    ];

    public function projects() {
        return ($this->belongsToMany("App\Models\Project", "soundblock_projects_metadata_moods", "mood_id", "project_id", "mood_id", "project_id")
                     ->whereNull("soundblock_projects_metadata_moods." . BaseModel::STAMP_DELETED)
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }
}

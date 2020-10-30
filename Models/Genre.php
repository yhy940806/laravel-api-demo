<?php

namespace App\Models;

class Genre extends BaseModel {
    protected $table = "soundblock_projects_metadata_genres";

    protected $primaryKey = "row_id";

    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        "row_id", "row_uuid", "project_id", "genre_id", BaseModel::STAMP_CREATED_BY, BaseModel::STAMP_UPDATED_BY, BaseModel::STAMP_DELETED_BY,
    ];

    public function projects() {
        return $this->hasMany("App\Models\Project", "project_id", "project_id");
    }
}

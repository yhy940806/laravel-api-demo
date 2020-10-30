<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Theme extends BaseModel {
    use SoftDeletes;

    protected $table = "soundblock_projects_metadata_themes";

    protected $primaryKey = "theme_id";

    protected string $uuid = "theme_uuid";

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        "row_id", "row_uuid", "theme_id", BaseModel::STAMP_CREATED_BY, BaseModel::STAMP_UPDATED_BY, BaseModel::STAMP_DELETED_BY,
    ];

}

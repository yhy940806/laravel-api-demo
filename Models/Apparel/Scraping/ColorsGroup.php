<?php

namespace App\Models\Apparel\Scraping;

use App\Models\BaseModel;

class ColorsGroup extends BaseModel
{
    const UUID = "group_uuid";

    protected $primaryKey = "group_id";

    protected $table = "x_scraping_apparel_colors_groups";

    protected $guarded = [];

    protected string $uuid = "group_uuid";

    protected $hidden = [
        "group_id",  BaseModel::DELETED_AT, BaseModel::STAMP_DELETED,
        BaseModel::STAMP_DELETED_BY, BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
    ];

    public function colors() {
        return $this->hasMany(Color::class, "group_id");
    }
}

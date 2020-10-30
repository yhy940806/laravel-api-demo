<?php

namespace App\Models\Apparel\Scraping;

use App\Models\BaseModel;

class Size extends BaseModel
{
    const UUID = "size_uuid";

    protected $primaryKey = "size_id";

    protected $table = "x_scraping_apparel_sizes";

    protected $guarded = [];

    protected string $uuid = "size_uuid";

    protected $hidden = [
        "size_id", "ascolour_id", BaseModel::DELETED_AT, BaseModel::STAMP_DELETED,
        BaseModel::STAMP_DELETED_BY, BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
    ];
}

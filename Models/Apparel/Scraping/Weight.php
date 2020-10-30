<?php

namespace App\Models\Apparel\Scraping;

use App\Models\BaseModel;

class Weight extends BaseModel
{
    const UUID = "weight_uuid";

    protected $primaryKey = "weight_id";

    protected $table = "x_scraping_apparel_weight";

    protected $guarded = [];

    protected string $uuid = "weight_uuid";

    protected $hidden = [
        "weight_uuid", "ascolour_id", BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
    ];
}

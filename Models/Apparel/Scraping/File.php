<?php

namespace App\Models\Apparel\Scraping;

use App\Models\BaseModel;

class File extends BaseModel
{
    const UUID = "file_uuid";

    protected $primaryKey = "file_id";

    protected $table = "x_scraping_apparel_files";

    protected $guarded = [];

    protected string $uuid = "file_uuid";

    protected $hidden = [
        "file_id", "file_url", BaseModel::DELETED_AT, BaseModel::STAMP_DELETED,
        BaseModel::STAMP_DELETED_BY, BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
    ];
}

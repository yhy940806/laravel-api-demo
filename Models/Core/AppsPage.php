<?php

namespace App\Models\Core;

use App\Models\Core\App;
use App\Models\BaseModel;

class AppsPage extends BaseModel
{
    const UUID = "page_uuid";

    protected $primaryKey = "page_id";

    protected $table = "apps_pages";

    protected $guarded = [];

    protected string $uuid = "page_uuid";

    protected $hidden = [
        "page_id", "app_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED,
        BaseModel::STAMP_DELETED_BY, BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
    ];

    protected $casts = [
        "page_url_params" => "array"
    ];

    public function app(){
        return $this->belongsTo(App::class, "app_id", "app_id");
    }
}

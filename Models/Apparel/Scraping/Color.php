<?php

namespace App\Models\Apparel\Scraping;

use App\Models\BaseModel;

class Color extends BaseModel
{
    const UUID = "color_uuid";

    protected $primaryKey = "color_id";

    protected $table = "x_scraping_apparel_colors";

    protected $guarded = [];

    protected string $uuid = "color_uuid";

    protected $hidden = [
        "color_id", "ascolour_id", BaseModel::DELETED_AT, BaseModel::STAMP_DELETED,
        BaseModel::STAMP_DELETED_BY, BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
    ];

    protected $casts = [
        "color_hash" => "array",
    ];

    public function colorGroup() {
        return $this->belongsTo(ColorsGroup::class, "group_id", "group_id");
    }

    public function thumbnail() {
        return $this->belongsTo(File::class, "file_id", "thumbnail_id");
    }

    public function product() {
        return $this->belongsToMany(Product::class, "x_scraping_apparel_products_colors", "color_id", "product_id", "color_id", "product_id")
                    ->withPivot("thumbnail_id");
    }
}

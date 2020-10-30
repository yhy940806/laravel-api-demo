<?php

namespace App\Models\Apparel\Scraping;

use App\Models\BaseModel;

class Style extends BaseModel
{
    const UUID = "style_uuid";

    protected $primaryKey = "style_id";

    protected $table = "x_scraping_apparel_styles";

    protected $guarded = [];

    protected string $uuid = "style_uuid";

    protected $hidden = [
        "style_id", "ascolour_id", BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
    ];

    public function products() {
        return $this->belongsToMany(Product::class,  "x_scraping_apparel_products_styles", "style_id", "product_id", "style_id", "product_id");
    }
}

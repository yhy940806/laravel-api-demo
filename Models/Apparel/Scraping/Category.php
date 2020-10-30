<?php

namespace App\Models\Apparel\Scraping;

use App\Models\BaseModel;

class Category extends BaseModel
{
    protected $guarded = [];

    const UUID = 'category_uuid';

    protected $table = 'x_scraping_apparel_categories';

    protected $primaryKey = 'category_id';

    protected string $uuid = 'category_uuid';

    protected $hidden = [
        "category_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT
    ];

    public function products() {
        return $this->belongsToMany(Product::class, "x_scraping_apparel_categories_products", "category_id", "product_id", "category_id", "product_id");
    }
}

<?php

namespace App\Models\Apparel\Scraping;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class ProductPrice extends BaseModel
{
    const UUID = "row_uuid";

    protected $primaryKey = "row_id";

    protected $table = "x_scraping_apparel_products_prices";

    protected $guarded = [];

    protected string $uuid = "price_uuid";

    protected $hidden = [
        "product_id", "ascolour_ref", "product_html", "product_url", BaseModel::DELETED_AT, BaseModel::STAMP_DELETED,
        BaseModel::STAMP_DELETED_BY, BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
    ];
}

<?php

namespace App\Models\Apparel;

use App\Models\BaseModel;

class ProductPrice extends BaseModel
{
    const UUID = "row_uuid";

    protected $guarded = [];

    protected $table = "merch_apparel_products_prices";

    protected $primaryKey = "row_id";

    protected $hidden = [
        "row_id", "product_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }
}

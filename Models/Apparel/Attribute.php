<?php

namespace App\Models\Apparel;

use App\Models\BaseModel;

class Attribute extends BaseModel
{
    protected $guarded = [];

    const UUID = "attribute_uuid";

    protected $table = "merch_apparel_attributes";

    protected string $uuid = "attribute_uuid";

    protected $primaryKey = "attribute_id";

    protected $hidden = [
        "attribute_id", "preview_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::CREATED_AT, BaseModel::STAMP_CREATED, BaseModel::STAMP_CREATED_BY,
        BaseModel::UPDATED_AT, BaseModel::STAMP_UPDATED, BaseModel::STAMP_UPDATED_BY, "pivot"
    ];

    public function products() {
        return $this->belongsToMany(Product::class, "merch_apparel_products_attributes", "attribute_id","product_id", "attribute_id",  "product_id")
            ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT);
    }

    public function category() {
        return $this->belongsTo(Category::class, "category_id", "category_id");
    }

    public function color() {
        return $this->belongsTo(File::class, "preview_id");
    }
}

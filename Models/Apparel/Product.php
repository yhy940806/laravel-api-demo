<?php

namespace App\Models\Apparel;

use App\Models\BaseModel;

class Product extends BaseModel
{
    const UUID = "product_uuid";

    protected $primaryKey = "product_id";

    protected $table = "merch_apparel_products";

    protected $guarded = [];

    protected string $uuid = "product_uuid";

    public $sortFields = [
        "title"      => "product_name",
        "price"      => "product_price",
        "popularity" => "product_weight"
    ];

    protected $hidden = [
        "product_id", "ascolour_ref", "ascolour_id", "product_html", "product_url", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED,
        BaseModel::STAMP_DELETED_BY, BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
    ];

    public function productSizes() {
        return $this->hasMany(ProductSize::class, "product_id");
    }

    public function productStyle() {
        return $this->hasMany(ProductStyle::class, "product_id");
    }

    public function currentStyle() {
        return $this->hasOne(ProductStyle::class, "product_id");
    }

    public function attributes() {
        return $this->belongsToMany(Attribute::class, "merch_apparel_products_attributes", "product_id", "attribute_id", "product_id", "attribute_id")
            ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT);
    }

    public function prices() {
        return $this->hasMany(ProductPrice::class, "product_id");
    }

    public function price() {
        return $this->hasOne(ProductPrice::class, "product_id")
                    ->where("range_min", 1)->where("range_max", "<=", 3);
    }

    public function files() {
        return $this->belongsToMany(File::class, "merch_apparel_products_files", "product_id", "file_id", "product_id", "file_id");
    }

    public function generalImages() {
        return $this->belongsToMany(File::class, "merch_apparel_products_files", "product_id", "file_id", "product_id", "file_id")
                    ->withPivot("file_type")->wherePivotIn("file_type", ["main_image", "front_image", "turn_image", "side_image", "back_image"]);
    }

    public function productColors(){
        return $this->hasMany(Color::class, "product_id");
    }

    public function relatedProducts() {
        return $this->belongsToMany(self::class, "merch_apparel_products_related", "product_id", "related_id", "product_id", "product_id");
    }
}

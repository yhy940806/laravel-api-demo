<?php

namespace App\Models\Apparel\Scraping;

use App\Models\BaseModel;

class Product extends BaseModel
{
    const UUID = "product_uuid";

    protected $primaryKey = "product_id";

    protected $table = "x_scraping_apparel_products";

    protected $guarded = [];

    protected string $uuid = "product_uuid";

    protected $hidden = [
        "product_id", "ascolour_ref", "product_html", "product_url", BaseModel::DELETED_AT, BaseModel::STAMP_DELETED,
        BaseModel::STAMP_DELETED_BY, BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
    ];

    public function files() {
        return $this->belongsToMany(File::class, "x_scraping_apparel_products_files", "product_id", "file_id", "product_id", "file_id")
            ->withPivot("type");
    }

    public function generalFiles() {
        return $this->belongsToMany(File::class, "x_scraping_apparel_products_files", "product_id", "file_id", "product_id", "file_id")
            ->withPivot("type")->wherePivotIn("type", ["main_image", "front_image", "turn_image", "side_image", "back_image", "pdf"]);
    }

    public function pdf() {
        return $this->belongsToMany(File::class, "x_scraping_apparel_products_files", "product_id", "file_id", "product_id", "file_id")
            ->withPivot("type")->wherePivot("type", "pdf");
    }

    public function colors() {
        return $this->belongsToMany(Color::class, "x_scraping_apparel_products_colors", "product_id", "color_id", "product_id", "color_id")
            ->withPivot("thumbnail_id");
    }

    public function sizes() {
        return $this->belongsToMany(Size::class, "x_scraping_apparel_products_sizes", "product_id", "size_id", "product_id", "size_id");
    }

    public function prices() {
        return $this->hasMany(ProductPrice::class, "product_id");
    }

    public function style() {
        return $this->belongsToMany(Style::class,  "x_scraping_apparel_products_styles", "product_id", "style_id", "product_id", "style_id");
    }

    public function weight() {
        return $this->belongsToMany(Weight::class, "x_scraping_apparel_products_weight", "product_id", "weight_id", "product_id", "weight_id");
    }

    public function categories() {
        return $this->belongsToMany(Category::class, "x_scraping_apparel_categories_products", "product_id", "category_id", "product_id", "category_id");
    }

    public function relatedProducts() {
        return $this->belongsToMany(self::class, "x_scraping_apparel_products_related", "product_id", "related_id", "product_id", "product_id");
    }
}

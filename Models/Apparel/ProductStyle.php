<?php

namespace App\Models\Apparel;

use App\Models\BaseModel;

class ProductStyle extends BaseModel
{
    const UUID = "row_uuid";

    protected $guarded = [];

    protected $table = "merch_apparel_products_colors";

    protected $primaryKey = "row_id";

    protected $hidden = [
        "row_id", "product_id", "image_id", "thumbnail_id", "preview_id", "ascolour_ref", BaseModel::DELETED_AT,
        BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY, BaseModel::CREATED_AT, BaseModel::UPDATED_AT
    ];

    protected $casts = [
        "color_hash" => "array",
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function images() {
        return $this->belongsToMany(File::class, "merch_apparel_products_files", "color_id", "file_id", "row_id", "file_id")
                    ->wherePivot("file_type", "original_image");
    }

    public function thumbnails() {
        return $this->belongsToMany(File::class, "merch_apparel_products_files", "color_id", "file_id", "row_id", "file_id")
                    ->wherePivot("file_type", "thumbnail");
    }

    public function color() {
        return $this->belongsTo(File::class, "preview_id");
    }

    public function originalImages() {
        return $this->belongsToMany(File::class, "merch_apparel_products_files" ,"color_id", "file_id")
            ->wherePivotIn("file_type", ["original_image", "original_image_back"])
            ->withPivot("file_type")->orderBy("file_type");
    }

    public function smallImages() {
        return $this->belongsToMany(File::class, "merch_apparel_products_files" ,"color_id", "file_id")
            ->wherePivotIn("file_type", ["small_image", "small_image_back"])
            ->withPivot("file_type")->orderBy("file_type");
    }
}

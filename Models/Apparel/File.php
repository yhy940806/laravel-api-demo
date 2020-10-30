<?php

namespace App\Models\Apparel;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Storage;

class File extends BaseModel
{
    const UUID = "file_uuid";

    protected $primaryKey = "file_id";

    protected $guarded = [];

    protected $table = "merch_apparel_files";

    protected $appends = ["full_url"];

    protected $hidden = [
        "file_id", "product_id", "image_id", "thumbnail_id", "ascolour_ref", "file_url", BaseModel::DELETED_AT,
        BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY, BaseModel::CREATED_AT, BaseModel::UPDATED_AT, "pivot"
    ];

    protected string $uuid = "file_uuid";

    public function getUrlAttribute(){
        if($this->file_type == 'pdf') {
            return "details/{$this->file_name}";
        }

        return "images/{$this->file_name}";
    }

    public function getFullUrlAttribute(){
        if($this->file_type == 'pdf') {
            return cloud_url("merch.apparel")."details/{$this->file_name}";
        }

        return cloud_url("merch.apparel")."images/{$this->file_name}";
    }
}

<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;
use App\Traits\StampAttribute;

class FileMusic extends BaseModel
{
    use StampAttribute;

    protected $table = "soundblock_files_music";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $guarded = [];

    protected $hidden = [
        "row_id", "file_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::UPDATED_AT, BaseModel::STAMP_UPDATED_BY, BaseModel::CREATED_AT, BaseModel::STAMP_CREATED_BY,
    ];

    public function file()
    {
        return($this->belongsTo(File::class, "file_id", "file_id"));
    }

    public function collections()
    {
        return($this->belongsToMany(Collection::class, "soundblock_collections_files", "file_id", "collection_id", "file_id", "collection_id")
                ->whereNull("soundblock_collections_files." . BaseModel::STAMP_DELETED)
                ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }
}

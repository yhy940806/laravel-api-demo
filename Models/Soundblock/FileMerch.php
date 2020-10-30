<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileMerch extends BaseModel
{
    //
    use SoftDeletes;

    protected $table = "soundblock_files_merch";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $hidden = [
        "row_id", "file_id"
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

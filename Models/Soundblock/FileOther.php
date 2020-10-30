<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;
use App\Traits\StampAttribute;

class FileOther extends BaseModel
{
    //
    use StampAttribute;

    protected $table = "soundblock_files_other";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $hidden = [
        "row_id", "file_id"
    ];

    public function file()
    {
        return $this->belongsTo(File::class, "file_id", "file_id");
    }

    public function collections()
    {
        return($this->belongsToMany(Collection::class, "soundblock_collections_files", "file_id", "collection_id", "file_id", "collection_id")
                    ->whereNull("soundblock_collections_files." . static::STAMP_DELETED)
                    ->withTimestamps(static::CREATED_AT, static::UPDATED_AT));
    }
}

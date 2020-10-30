<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;
use App\Traits\StampAttribute;

class FileVideo extends BaseModel
{
    use StampAttribute;

    protected $table = "soundblock_files_videos";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $hidden = [
        "music_id",
    ];

    public function file()
    {
        return $this->belongsTo(File::class, "file_id", "file_id");
    }

    public function track()
    {
        return($this->hasOne(File::class, "file_id", "music_id"));
    }

    public function collections()
    {
        return($this->belongsToMany(Collection::class, "soundblock_collections_files", "file_id", "collection_id", "file_id", "collection_id")
                    ->whereNull("soundblock_collections_files." . static::STAMP_DELETED)
                    ->withTimestamps(static::CREATED_AT, static::UPDATED_AT));
    }

}

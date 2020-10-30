<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;

class FileHistory extends BaseModel
{
    //
    protected $table = "soundblock_files_history";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $hidden = [
        "row_id", "file_id", "collection_id", "parent_id"
    ];

    public function parent()
    {
        return($this->belongsTo(FileHistory::class, "parent_id", "file_id"));
    }

    public function children()
    {
        return($this->hasMany(FileHistory::class, "file_id", "parent_id"));
    }

    public function collection()
    {
        return($this->belongsTo(Collection::class, "collection_id", "collection_id"));
    }

    public function file()
    {
        return($this->belongsTo(File::class, "file_id", "file_id"));
    }
}

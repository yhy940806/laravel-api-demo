<?php

namespace App\Models;

use App\Models\Soundblock\{File, Collection};

class FileHistory extends BaseModel {
    protected $table = "soundblock_files_history";

    protected $primaryKey = "row_id";

    protected $hidden = [
        "row_id", "file_id", "parent_id", "collection_id",
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
    ];

    public function file() {
        return ($this->belongsTo(File::class, "file_id", "file_id"));
    }

    public function collection() {
        return ($this->belongsTo(Collection::class, "collection_id", "collection_id"));
    }

    public function parent() {
        return ($this->belongsTo(FileHistory::class, "row_id", "parent_id"));
    }

    public function children() {
        return ($this->hasMany(FileHistory::class, "parent_id", "row_id"));
    }
}

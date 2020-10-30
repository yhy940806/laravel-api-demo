<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;
use App\Models\Soundblock\Pivot\FileHistoryPivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends BaseModel
{
    use SoftDeletes;

    protected $table = "soundblock_collections";

    protected $primaryKey = "collection_id";

    protected string $uuid = "collection_uuid";

    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        "collection_id", "project_id",  "project",
        BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT
    ];

    public function files()
    {
        return($this->belongsToMany(File::class, "soundblock_collections_files", "collection_id", "file_id", "collection_id", "file_id")
                ->withPivot("file_uuid", "collection_uuid")
                ->whereNull("soundblock_collections_files." . BaseModel::STAMP_DELETED)
                ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function musics()
    {
        return($this->belongsToMany(FileMusic::class, "soundblock_collections_files", "collection_id", "file_id", "collection_id", "file_id")
                ->whereNull("soundblock_collections_files." . BaseModel::STAMP_DELETED)
                ->orderby("file_track", "asc")
                ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function videos()
    {
        return($this->belongsToMany(FileVideo::class, "soundblock_collections_files", "collection_id", "file_id", "collection_id", "file_id")
                ->whereNull("soundblock_collections_files." . BaseModel::STAMP_DELETED)
                ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function merches()
    {
        return($this->belongsToMany(FileMerch::class, "soundblock_collections_files", "collection_id", "file_id", "collection_id", "file_id")
                ->whereNull("soundblock_collections_files." . BaseModel::STAMP_DELETED)
                ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function others()
    {
        return($this->belongsToMany(FileOther::class, "soundblock_collections_files", "collection_id", "file_id", "collection_id", "file_id")
                ->whereNull("soundblock_collections_files." . BaseModel::STAMP_DELETED)
                ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function project()
    {
        return($this->belongsTo(Project::class, "project_id", "project_id"));
    }

    public function directories()
    {
        return($this->belongsToMany(Directory::class, "soundblock_collections_directories", "collection_id", "directory_id", "collection_id", "directory_id")
                ->whereNull("soundblock_collections_directories." . BaseModel::STAMP_DELETED)
                ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function collectionFilesHistory()
    {
        return($this->belongsToMany(File::class, "soundblock_files_history", "collection_id", "file_id", "collection_id", "file_id")
                    ->using(FileHistoryPivot::class)
                    ->whereNull("soundblock_files_history." . BaseModel::STAMP_DELETED)
                    ->withPivot("collection_uuid", "file_uuid", "file_action", "file_category", "file_memo")
                    ->orderBy(BaseModel::STAMP_CREATED, "asc")
                    ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function history()
    {
        return($this->hasOne(CollectionHistory::class, "collection_id", "collection_id"));
    }

    public function deployment()
    {
        return($this->belongsTo(Deployment::class, "collection_id", "colelction_id"));
    }

    public function countFiles()
    {
        return($this->files()->count());
    }

    public function countDirectories()
    {
        return($this->directories()->count());
    }

    public function size()
    {
        return($this->files()->sum("file_size"));
    }
}

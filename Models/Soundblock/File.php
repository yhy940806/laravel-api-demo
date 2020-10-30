<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends BaseModel
{
    //
    use SoftDeletes;

    protected $table = "soundblock_files";

    protected $primaryKey = "file_id";

    protected string $uuid = "file_uuid";

    protected $guarded = [];

    protected $hidden = [
        "directory_id", "pivot", "file_id", "music", "video", "merch", "other",
        "file_isrc", "file_duration", "file_track", "music_id", "music_uuid",
        BaseModel::MODIFIED_AT, BaseModel::STAMP_MODIFIED, BaseModel::STAMP_MODIFIED_BY,
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
    ];

    protected $observables = [
        "modified"
    ];

    protected $appends = [
        "meta"
    ];

    public function collections()
    {
        return($this->belongsToMany(Collection::class, "soundblock_collections_files", "file_id", "collection_id", "file_id", "collection_id")
                ->withPivot("file_uuid", "collection_uuid")
                ->whereNull("soundblock_collections_files." . static::STAMP_DELETED)
                ->withTimestamps(static::CREATED_AT, static::UPDATED_AT));
    }

    public function music()
    {
        return($this->hasOne(FileMusic::class, "file_id", "file_id"));
    }

    public function other()
    {
        return($this->hasOne(FileOther::class, "file_id", "file_id"));
    }

    public function video()
    {
        return($this->hasOne(FileVideo::class, "file_id", "file_id"));
    }

    public function merch()
    {
        return($this->hasOne(FileMerch::class, "file_id", "file_id"));
    }

    public function directory()
    {
        return($this->belongsTo(Directory::class, "directory_id", "directory_id"));
    }

    public function collectionshistory()
    {
        return($this->belongsToMany(Collection::class, "soundblock_files_history", "file_id", "collection_id", "file_id", "collection_id")
                    ->whereNull("soundblock_files_history." . static::STAMP_DELETED)
                    ->withPivot("collection_uuid", "file_uuid", "file_action", "file_category", "file_memo")
                    ->orderBy(static::STAMP_CREATED, "asc")
                    ->withTimestamps(static::CREATED_AT, static::UPDATED_AT));
    }

    public function modified()
    {
        $this->fireModelEvent("modified", false);
    }

    public function getMetaAttribute()
    {
        $objForCategory = $this->{$this->file_category};
        $meta = [];
        if (!$objForCategory)
            return($meta);
        switch ($this->file_category) {
            case "music" : {
                $meta = [
                    "type" => "music",
                    "file_track" => $objForCategory->file_track,
                    "file_duration" => $objForCategory->file_duration,
                    "file_isrc" => $objForCategory->file_isrc
                ];
                break;
            }
            case "video": {
                $meta = [
                    "type" => "video",
                    "track_uuid" => $objForCategory->track ? $objForCategory->track->file_uuid : null,
                    "track" => $objForCategory->track ? $objForCategory->track->only(["file_name", "file_title", "file_sortby"]) : null,
                    "file_isrc" => $objForCategory->file_isrc
                ];
                break;
            }
            case "merch": {
                $meta = [
                    "type" => "merch",
                    "file_sku" => $objForCategory->file_sku
                ];
                break;
            }
            case "order": {
                $meta = [
                    "type" => "order"
                ];
                break;
            }
        }

        return($meta);
    }

}

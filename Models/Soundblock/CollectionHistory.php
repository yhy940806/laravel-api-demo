<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollectionHistory extends BaseModel
{
    //
    use SoftDeletes;

    protected $table = "soundblock_collections_history";

    protected $primaryKey = "history_id";

    protected string $uuid = "history_uuid";

    protected $guarded = [];

    protected $hidden = [
        "history_id", "collection_id",
        BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT
    ];

    public function collection()
    {
        return($this->belongsTo(Collection::class, "collection_id", "collection_id"));
    }
}

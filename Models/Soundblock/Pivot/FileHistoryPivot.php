<?php

namespace App\Models\Soundblock\Pivot;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\BaseModel;

class FileHistoryPivot extends Pivot
{

    protected $hidden = [
        "row_id", "file_id", "parent_id", "collection_id",
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
    ];
}

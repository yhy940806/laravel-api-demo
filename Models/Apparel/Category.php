<?php

namespace App\Models\Apparel;

use App\Models\BaseModel;

class Category extends BaseModel
{
    protected $guarded = [];

    const UUID = 'category_uuid';

    protected $table = 'merch_apparel_categories';

    protected $primaryKey = 'category_id';

    protected string $uuid = 'category_uuid';

    protected $hidden = [
        "category_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT
    ];

    public function attributes() {
        return $this->hasMany(Attribute::class, 'category_id', 'category_id');
    }
}

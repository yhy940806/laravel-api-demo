<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class VendorPlatform extends BaseModel {
    use SoftDeletes;

    protected $primaryKey = "platform_id";

    protected string $uuid = "platform_uuid";

    protected $table = "common_vendors_platforms";

    protected $hidden = [
        "id", "platform_id", BaseModel::STAMP_CREATED_BY, BaseModel::STAMP_UPDATED_BY, BaseModel::STAMP_DELETED_BY,
    ];

    public function vendor() {
        return ($this->belongsTo(Vendor::class));
    }

}

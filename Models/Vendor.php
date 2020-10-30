<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends BaseModel {
    use SoftDeletes;

    protected $primaryKey = "vendor_id";

    protected string $uuid = "vendor_uuid";

    protected $table = "common_vendors";

    protected $hidden = [
        "vendor_id", BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
    ];

    public function vendorPlatform() {
        return ($this->hasMany(VendorPlatform::class));
    }

}

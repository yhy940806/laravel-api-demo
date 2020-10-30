<?php

namespace App\Models\Apparel;

use App\Models\BaseModel;

class Color extends BaseModel
{
    protected $primaryKey = "row_id";

    protected $table = "merch_apparel_products_colors";

    protected $guarded = [];

    protected string $uuid = "row_uuid";
}

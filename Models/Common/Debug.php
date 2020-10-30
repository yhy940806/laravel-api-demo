<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class Debug extends Model
{
    //
    protected $table = "debug";

    protected $primaryKey = "id";

    protected $casts = [
        "json" => "array"
    ];

    public $timestamps = false;
}

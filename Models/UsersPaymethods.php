<?php

namespace App\Models;

use App\Traits\EncryptsAttributes;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsersPaymethods extends BaseModel {
    use SoftDeletes;
    use EncryptsAttributes;

    protected $table = "soundblock_users_paymethods";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected array $encrypts = ["user_paypal", "user_bankname", "user_bankaccount", "user_bankroute"];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        "row_id", BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
    ];

    protected $fillable = [

    ];
}

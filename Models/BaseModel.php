<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BaseScalable;
use App\Models\Casts\StampCast;
use Auth;
use Util;

class BaseModel extends Model {
    use SoftDeletes;
    use BaseScalable;

    const CREATED_AT = "stamp_created_at";
    const STAMP_CREATED = "stamp_created";
    const IDX_STAMP_CREATED = "idx_stamp-created";
    const STAMP_CREATED_BY = "stamp_created_by";

    const UPDATED_AT = "stamp_updated_at";
    const STAMP_UPDATED = "stamp_updated";
    const IDX_STAMP_UPDATED = "idx_stamp-updated";
    const STAMP_UPDATED_BY = "stamp_updated_by";

    const DELETED_AT = "stamp_deleted_at";
    const STAMP_DELETED = "stamp_deleted";
    const IDX_STAMP_DELETED = "idx_stamp-deleted";
    const STAMP_DELETED_BY = "stamp_deleted_by";

    const MODIFIED_AT = "stamp_modified_at";
    const STAMP_MODIFIED = "stamp_modified";
    const IDX_STAMP_MODIFIED = "idx_stamp-modified";
    const STAMP_MODIFIED_BY = "stamp_modified_by";

    const VISITED_AT = "stamp_visited_at";

    const STAMP_VISITED = "stamp_visited";
    const IDX_STAMP_VISITED = "idx_stamp-visited";
    const STAMP_VISITED_BY = "stamp_visited_by";

    const EXITED_AT = "stamp_exited_at";
    const STAMP_EXITED = "stamp_exited";
    const IDX_STAMP_EXITED = "idx_stamp-exited";
    const STAMP_EXITED_BY = "stamp_exited_by";

    const TRANSACTION_AT = "stamp_transaction_at";
    const STAMP_TRANSACTION = "stamp_transaction";
    const STAMP_TRANSACTION_BY = "stamp_transaction_by";
    const IDX_STAMP_TRANSACTION = "idx_stamp-transaction";

    const DISCOUNT_AT = "stamp_discount_at";
    const STAMP_DISCOUNT = "stamp_discount";
    const STAMP_DISCOUNT_BY = "stamp_discount_by";
    const IDX_STAMP_DISCOUNT = "idx_stamp-discount";

    protected string $uuid = "uuid";

    protected $hidden = [
        BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::UPDATED_AT, BaseModel::STAMP_UPDATED_BY, BaseModel::CREATED_AT, BaseModel::STAMP_CREATED_BY,
    ];

    protected $casts = [
        BaseModel::STAMP_CREATED_BY => StampCast::class,
        BaseModel::STAMP_UPDATED_BY => StampCast::class,
    ];

    protected $guarded = [];

    protected static function boot() {
        parent::boot();

        static::creating(function ($model) {
            $model->{static::STAMP_CREATED} = time();
            $model->{static::STAMP_UPDATED} = time();

            if (Auth::id()) {
                $model->{static::STAMP_CREATED_BY} = Auth::id();
                $model->{static::STAMP_UPDATED_BY} = Auth::id();
            } else {
                $model->{static::STAMP_CREATED_BY} = 1;
                $model->{static::STAMP_UPDATED_BY} = 1;
            }
        });

        static::updating(function ($model) {
            $model->{static::STAMP_UPDATED} = time();

            if (Auth::id()) {
                $model->{static::STAMP_UPDATED_BY} = Auth::id();
            } else {

            }
        });

        static::deleting(function ($model) {
            $model->{static::STAMP_DELETED} = time();
            $model->{static::DELETED_AT} = Util::now();
            $model->{static::STAMP_DELETED_BY} = Auth::id();
            $model->save();
        });
    }
}

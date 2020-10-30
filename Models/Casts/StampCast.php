<?php

namespace App\Models\Casts;

use App\Models\{BaseModel, UserContactEmail};
use App\Traits\StampCache;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class StampCast implements CastsAttributes
{
    use StampCache;

    public function get($model, $key, $value, $attributes)
    {
        if (array_search($key ,[BaseModel::STAMP_CREATED_BY, BaseModel::STAMP_UPDATED_BY, UserContactEmail::STAMP_EMAIL_BY, BaseModel::STAMP_DISCOUNT_BY]) !== false) {
            return $this->getStampBy($value);
        } else {
            return($value);
        }
    }

    public function set($model, $key, $value, $attributes)
    {
        return [$key => $value];
    }
}

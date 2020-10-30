<?php

namespace App\Traits;

use Auth;
use Util;
use App\Models\BaseModel;

trait StampAttribute {
    protected static function boot() {
        parent::boot();

        static::creating(function ($model) {
            $model->{BaseModel::STAMP_CREATED} = time();
            $model->{BaseModel::STAMP_UPDATED} = time();

            if (Auth::user()) {
                $model->{BaseModel::STAMP_CREATED_BY} = Auth::id();
                $model->{BaseModel::STAMP_UPDATED_BY} = Auth::id();
            }
        });

        static::updating(function ($model) {
            $model->{BaseModel::STAMP_UPDATED} = time();

            if (Auth::user()) {
                $model->{BaseModel::STAMP_UPDATED_BY} = Auth::id();
            }
        });

        static::deleting(function ($model) {
            $model->{BaseModel::STAMP_DELETED_BY} = time();
            $model->{BaseModel::DELETED_AT} = Util::now();

            if (Auth::user()) {
                $model->{BaseModel::STAMP_DELETED_BY} = Auth::id();
            }
            $model->save();
        });
    }
}

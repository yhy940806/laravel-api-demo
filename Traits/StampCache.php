<?php

namespace App\Traits;

use Cache;
use Illuminate\Database\Eloquent\Model;
use App\Models\{User, BaseModel, UserContactEmail};

trait StampCache {

    /**
     * @param Model $model
     * @param string $strInclude
     * @return array
     */
    public function stamp(Model $model, ?string $strInclude = null): array {
        $defaultStamp = [
            BaseModel::STAMP_CREATED    => $model->{BaseModel::STAMP_CREATED},
            BaseModel::STAMP_CREATED_BY => $model->{BaseModel::STAMP_CREATED_BY},
            BaseModel::STAMP_UPDATED    => $model->{BaseModel::STAMP_UPDATED},
            BaseModel::STAMP_UPDATED_BY => $model->{BaseModel::STAMP_UPDATED_BY},
        ];
        if (!is_null($strInclude)) {
            switch (strtolower($strInclude)) {
                case "email" :
                    {
                        $subStamp = [
                            UserContactEmail::STAMP_EMAIL    => $model->{UserContactEmail::STAMP_EMAIL},
                            UserContactEmail::STAMP_EMAIL_BY => isset($model->{UserContactEmail::STAMP_EMAIL_BY}) ?
                                $this->getStampBy($model->{UserContactEmail::STAMP_EMAIL_BY}) : null,
                        ];
                        break;
                    }
                    break;
            }

            $defaultStamp = array_merge($defaultStamp, $subStamp);
        }

        return ($defaultStamp);
    }

    /**
     * @param int $value
     * @return mixed
     */
    public function getStampBy(?int $value = null) {
        if (is_null($value)) {
            return (["uuid" => null, "name" => ""]);
        }
        $user = User::findOrFail($value);

        return (Cache::remember("users.user_id." . $value, config("constant.cache_ttl"), function () use ($value) {
            /** @var User */
            $user = User::findOrFail($value);

            return (["uuid" => $user->user_uuid, "name" => $user->name]);
        }));
    }
}

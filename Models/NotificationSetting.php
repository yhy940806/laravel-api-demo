<?php

namespace App\Models;

use App\Models\Casts\StampCast;
use App\Models\Core\App;
use Illuminate\Database\Eloquent\SoftDeletes;
use Client;

class NotificationSetting extends BaseModel {
    use SoftDeletes;

    const APP_FLAGS = [
        "flag_apparel", "flag_arena", "flag_catalog", "flag_io", "flag_merchandising", "flag_music",
        "flag_office", "flag_soundblock",
    ];

    protected $table = "notifications_users_settings";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $casts = [
        "user_setting"              => "array",
        BaseModel::STAMP_CREATED_BY => StampCast::class,
        BaseModel::STAMP_UPDATED_BY => StampCast::class,
    ];

    protected $hidden = [
        "row_id", "row_uuid", "user_id", "app_id", "user_setting", BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY, BaseModel::DELETED_AT,
    ];

    protected $appends = [
        "setting",
    ];

    public function setPropertiesAttribute($value) {
        $properties = [];

        foreach ($value as $array_item) {
            if (!is_null($array_item["key"])) {
                $properties[] = $array_item;
            }
        }

        $this->attributes["properties"] = json_encode($properties);
    }

    public function user() {
        return ($this->belongsTo(User::class, "user_id", "user_id"));
    }

    public function app() {
        return $this->belongsTo(App::class, "app_id", "app_id");
    }

    public function getSettingAttribute() {
        $platform = strtolower(Client::platform()) == "web" ? "web" : "mobile";
        $setting = $this->user_setting;
        return [
            "play_sound" => $setting["play_sound"],
            "position"   => $setting["position"][$platform],
            "show_time"  => isset($setting["show_time"]) ? $setting["show_time"] : 5,
            "per_page"   => isset($setting["per_page"]) ? $setting["per_page"] : 10,
        ];
    }
}

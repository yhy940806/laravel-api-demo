<?php

namespace App\Helpers;

use App\Models\Core\Auth\AuthPermission;
use Illuminate\Support\Collection;

class Constant
{
    const __MACOSX = "__MACOSX";
    const MetaFolder = ".meta";

    const MusicExtension = ["mp3", "wav"];
    const MusicCategory = "music";
    const VideoExtension = ["mp4", "avi", "3gp", "mov"];
    const VideoCategory = "video";
    const MerchExtension = ["ai", "psd"];
    const MerchCategory = "merch";
    const OtherCategory = "other";

    const Separator = "/";

    const NOT_EXIST = 0;
    const EXIST = 1;
    const SOFT_DELETED =2;
    /**
     * @return array
     */
    public static function service_level_permission_names() : array
    {
        return(config("constant.service.permissions"));
    }

    /**
     * @return array
     */
    public static function project_level_permission_names() : array
    {
        return(config("constant.soundblock.project.permissions"));
    }

    /**
     * @return array
     */
    public function user_level_permissions_names() : array
    {
        return(static::user_level_permissions()->pluck("permission_name")->toArray());
    }

    /**
     * @return Collection
     */
    public static function service_level_permissions() : Collection
    {
        return(AuthPermission::whereIn("permission_name", static::service_level_permission_names())->get());
    }

    /**
     * @return Collection
     */
    public static function project_level_permissions() : Collection
    {
        return(AuthPermission::whereIn("permission_name", static::project_level_permission_names())->get());
    }

    /**
     * @return Collection
     */
    public static function user_level_permissions() : Collection
    {
        $projectLevelPermissionNames = static::project_level_permission_names();
        array_push($projectLevelPermissionNames, "App.Office.Admin.Default");

        return(AuthPermission::whereNotIn("permission_name", $projectLevelPermissionNames)->get());
    }
}

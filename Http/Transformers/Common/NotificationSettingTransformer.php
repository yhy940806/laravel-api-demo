<?php

namespace App\Http\Transformers\Common;

use App\Http\Transformers\BaseTransformer;
use App\Models\NotificationSetting;
use App\Traits\StampCache;
use Client;

class NotificationSettingTransformer extends BaseTransformer
{
    use StampCache;

    /**
     * @param NotificationSetting $objSetting
     * @return array
     */
    public function transform(NotificationSetting $objSetting)
    {
        $response = [
            "flag_apparel"          => $objSetting->flag_apparel,
            "flag_arena"            => $objSetting->flag_arena,
            "flag_catalog"          => $objSetting->flag_catalog,
            "flag_io"               => $objSetting->flag_io,
            "flag_merchandising"    => $objSetting->flag_merchandising,
            "flag_music"            => $objSetting->flag_music,
            "flag_office"           => $objSetting->flag_office,
            "flag_soundblock"       => $objSetting->flag_soundblock,
            "setting"               => $objSetting->setting
        ];

        return (array_merge($response, $this->stamp($objSetting)));
    }
}

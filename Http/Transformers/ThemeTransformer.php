<?php
namespace App\Http\Transformers;

use App\Models\Theme;
use League\Fractal\TransformerAbstract;

class ThemeTransformer extends TransformerAbstract
{
    public function transform(Theme $theme)
    {
        return([
            "theme_uuid" => $theme->theme_uuid,
            "theme_name" => $theme->theme_name,
        ]);
    }
}

<?php

namespace App\Http\Transformers\Soundblock;

use App\Http\Transformers\BaseTransformer;
use App\Models\BaseModel;
use App\Models\Soundblock\FileMusic;
use App\Models\User;
use App\Traits\StampCache;
use Cache;

class FileMusicTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(FileMusic $objFileMusic)
    {

        $response = [
            "file_track" => $objFileMusic->file_track,
            "file_duration" => $objFileMusic->file_duration,
            "file_isrc" => $objFileMusic->file_isrc,
        ];

        if ($objFileMusic->file)
        {
            $objFile = $objFileMusic->file;
            $response = array_merge($response, $objFile->toArray());
        }
        $response = array_merge($response, $this->stamp(($objFileMusic)));

        return($response);
    }

    public function includeFile(FileMusic $objFileMusic)
    {
        return($this->item($objFileMusic->file, new FileTransformer));
    }
}

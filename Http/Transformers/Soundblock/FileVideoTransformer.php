<?php

namespace App\Http\Transformers\Soundblock;

use App\Http\Transformers\BaseTransformer;
use App\Models\Soundblock\FileVideo;
use App\Traits\StampCache;

class FileVideoTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(FileVideo $objFileVideo)
    {

        $response = [
            "track" => $objFileVideo->track? $objFileVideo->track->file_title : null,
            "file_isrc" => $objFileVideo->file_isrc,
        ];

        if ($objFileVideo->file)
        {
            $objFile = $objFileVideo->file;
            array_merge($response, $objFile->toArray());
        }
        $response = array_merge($response, $this->stamp($objFileVideo));
        return($response);
    }

    public function includeTrack(FileVideo $objFileVideo)
    {
        if (!is_null($objFileVideo->track))
            return($this->item($objFileVideo->track->music, new FileMusicTransformer));
    }
}

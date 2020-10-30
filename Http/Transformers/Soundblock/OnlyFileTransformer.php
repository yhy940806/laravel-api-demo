<?php

namespace App\Http\Transformers\Soundblock;

use App\Http\Transformers\BaseTransformer;
use App\Models\Soundblock\File;
use App\Traits\StampCache;

class OnlyFileTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(File $objFile)
    {

        $response = [
            "file_id" => $objFile->file_id,
            "file_uuid" => $objFile->file_uuid,
            "file_name" => $objFile->file_name,
            "file_path" => $objFile->file_path,
            "file_title" => $objFile->file_title,
            "file_category" => $objFile->file_category,
            "file_sortby" => $objFile->file_sortby,
            "file_size" => $objFile->file_size,
            "meta"  => $objFile->meta,
            File::STAMP_CREATED => $objFile->{File::STAMP_CREATED},
            File::STAMP_CREATED_BY => $objFile->{File::STAMP_CREATED_BY},
            File::STAMP_UPDATED => $objFile->{File::STAMP_UPDATED},
            File::STAMP_UPDATED_BY => $objFile->{File::STAMP_UPDATED_BY}
        ];

        if ($objFile->pivot)
        {
            if ($objFile->pivot->file_action && $objFile->pivot->file_category)
            {
                $response["file_action"] = $objFile->pivot->file_action;
                $response["file_category"] = $objFile->pivot->file_category;
                $response["file_memo"] = $objFile->pivot->file_memo;
            }
        }

        return($response);
    }

    public function includeMusic(File $objFile)
    {
        return($this->item($objFile->music, new FileMusicTransformer));
    }

    public function includeVideo(File $objFile)
    {
        return($this->item($objFile->video, new FileVideoTransformer(["track"])));
    }

    public function includeMerch(File $objFile)
    {
        return($this->item($objFile->merch, new FileMerchTransformer));
    }

    public function includeOther(File $objFile)
    {
        return($this->item($objFile->other, new FileOtherTransformer));
    }
}

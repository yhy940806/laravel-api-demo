<?php

namespace App\Http\Transformers\Soundblock;

use App\Http\Transformers\BaseTransformer;
use App\Models\Soundblock\FileMerch;
use App\Traits\StampCache;

class FileMerchTransformer extends BaseTransformer
{
    use StampCache;

    public function transform(FileMerch $objFileMerch)
    {

        $arrStamp = $this->stamp($objFileMerch);

        if ($objFileMerch->file)
        {
            $objFile = $objFileMerch->file;
            $arrStamp = array_merge($arrStamp, $objFile->toArray());
        }

        return($arrStamp);
    }
}

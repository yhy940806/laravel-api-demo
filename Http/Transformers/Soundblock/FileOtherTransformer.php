<?php
namespace App\Http\Transformers\Soundblock;

use App\Http\Transformers\BaseTransformer;
use App\Models\Soundblock\FileOther;
use App\Traits\StampCache;

class FileOtherTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(FileOther $objFileOther)
    {
        $response = array();
        if ($objFileOther->file)
        {
            $response = $objFileOther->file->toArray();
        }
        $response = array_merge($response, $this->stamp($objFileOther));

        return($response);
    }
}

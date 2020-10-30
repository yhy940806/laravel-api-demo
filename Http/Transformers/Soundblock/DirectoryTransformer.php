<?php

namespace App\Http\Transformers\Soundblock;

use App\Http\Transformers\BaseTransformer;
use App\Models\Soundblock\Directory;
use App\Traits\StampCache;
use Cache;

class DirectoryTransformer extends BaseTransformer
{
    use StampCache;

    public function transform(Directory $objDir)
    {
        $response = [
            "directory_uuid" => $objDir->directory_uuid,
            "directory_name" => $objDir->directory_name,
            "directory_path" => $objDir->directory_path,
            "directory_sortby" => $objDir->directory_sortby,
        ];

        return(array_merge($response, $this->stamp($objDir)));
    }
}

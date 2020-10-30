<?php

namespace App\Http\Transformers\Common;

use App\Http\Transformers\BaseTransformer;
use App\Http\Transformers\Soundblock\FileTransformer;
use App\Models\Soundblock\FileHistory;
use App\Traits\StampCache;

class FileHistoryTransformer extends BaseTransformer
{

    use StampCache;
    public function transform(FileHistory $objHistory)
    {
        $response = [
            "row_uuid" => $objHistory->row_uuid,
            "parent_uuid" => $objHistory->parent_uuid,
            "file_uuid" => $objHistory->file_uuid,
            "file_action" => $objHistory->file_action,
            "file_category" => $objHistory->file_category,
            "file_memo" => $objHistory->file_memo,
        ];

        return(array_merge($response, $this->stamp($objHistory)));
    }

    public function includeFile(FileHistory $objHistory)
    {
        return($this->item($objHistory->file, new FileTransformer()));
    }
}

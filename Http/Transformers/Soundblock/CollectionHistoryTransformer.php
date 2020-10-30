<?php

namespace App\Http\Transformers\Soundblock;

use App\Http\Transformers\BaseTransformer;
use App\Models\Soundblock\CollectionHistory;
use App\Traits\StampCache;

class CollectionHistoryTransformer extends BaseTransformer
{

    use StampCache;

    public function transform(CollectionHistory $objCollectionHistory)
    {
        $response = [
            "history_uuid" => $objCollectionHistory->history_uuid,
            "history_category" => $objCollectionHistory->history_category,
            "history_size" => $objCollectionHistory->history_size,
            "file_action" => $objCollectionHistory->file_action,
            "history_comment" => $objCollectionHistory->history_comment,
        ];

        return(array_merge($response, $this->stamp($objCollectionHistory)));
    }
}

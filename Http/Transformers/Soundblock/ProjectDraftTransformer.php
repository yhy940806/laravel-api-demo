<?php

namespace App\Http\Transformers\Soundblock;

use App\Http\Transformers\BaseTransformer;
use App\Http\Transformers\ServiceTransformer;
use App\Models\Soundblock\ProjectDraft;
use App\Traits\StampCache;

class ProjectDraftTransformer extends BaseTransformer
{
    use StampCache;

    public function transform(ProjectDraft $objDraft)
    {
        $response = [
            "draft_uuid" => $objDraft->draft_uuid,
            "draft_json" => $objDraft->draft_json,
        ];

        return(array_merge($response, $this->stamp($objDraft)));
    }

    public function includeService(ProjectDraft $objDraft)
    {
        return($this->item($objDraft->service, new ServiceTransformer));
    }
}

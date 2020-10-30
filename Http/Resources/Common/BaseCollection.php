<?php

namespace App\Http\Resources\Common;

use App\Traits\JsonResponsable;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BaseCollection extends ResourceCollection
{

    use JsonResponsable;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->setStatusData();
        return [
            "data" => $this->collection,
        ];
    }
}

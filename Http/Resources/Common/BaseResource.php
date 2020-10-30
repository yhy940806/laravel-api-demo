<?php

namespace App\Http\Resources\Common;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\JsonResponsable;

class BaseResource extends JsonResource
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
        return parent::toArray($request);
    }
}

<?php

namespace App\Http\Resources\Common;

class UserCollection extends BaseCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->setStatusData();
        return ["data" => UserResource::collection($this->collection)];
    }
}

<?php

namespace App\Http\Resources\User;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return([
            "user_uuid"             => $this->user_uuid,
            "name"                  => $this->name,
            "primary_email"         => $this->primary_email,
            "primary_phone"         => $this->primary_phone,
            "primary_alias"         => $this->primary_alias,
            "avatar"                => $this->avatar,
            User::STAMP_CREATED     => $this->{User::STAMP_CREATED},
            User::STAMP_CREATED_BY  => $this->{User::STAMP_CREATED_BY},
            User::STAMP_UPDATED     => $this->{User::STAMP_UPDATED},
            User::STAMP_UPDATED_BY  => $this->{User::STAMP_UPDATED_BY}
        ]);
    }
}

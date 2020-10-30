<?php

namespace App\Http\Resources\Common;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $res = [
            "user_uuid"             => $this->user_uuid,
            "name"                  => $this->name,
            "primary_email"         => $this->primary_email,
            "avatar"                => $this->avatar,
            User::STAMP_CREATED     => $this->{User::STAMP_CREATED},
            User::STAMP_CREATED_BY  => $this->{User::STAMP_CREATED_BY},
            User::STAMP_UPDATED     => $this->{User::STAMP_UPDATED},
            User::STAMP_UPDATED_BY  => $this->{User::STAMP_UPDATED_BY},
        ];
        if (isset($this->groupsWithPermissions)) {
            $res["groups_with_permissions"] = $this->groupsWithPermissions;
        }
        return($res);
    }
}

<?php

namespace App\Http\Resources\Soundblock;

use App\Http\Resources\Common\BaseResource;
use App\Models\Soundblock\Contract;

class ContractResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var \Illuminate\Database\Eloquent\Collection */
        $contractUsers = $this->users()->with(["permissionsInGroup"])->wherePivot("contract_version", $this->contract_version)->get();
        $contractUsers = $contractUsers->map(function ($item) {
            return array_merge($item->toArray(), ["contract" => [
                "user_payout"       => $item->pivot->user_payout,
                "contract_status"   => $item->pivot->contract_status
            ]]);
        });
        /** @var \Illuminate\Database\Eloquent\Collection */
        $contractInvites = $this->contractInvites()->wherePivot("contract_version", $this->contract_version)->get();
        $contractInvites = $contractInvites->map(function ($item) {
            return array_merge($item->toArray(), [
                "contract"      => [
                    "contract_uuid"     => $item->pivot->contract_uuid,
                    "user_payout"       => $item->pivot->user_payout
                ]
            ]);
        });
        $this->setStatusData();
        return([
            "contract_uuid"             => $this->contract_uuid,
            "flag_status"               => $this->flag_status,
            Contract::STAMP_CREATED     => $this->{Contract::STAMP_CREATED},
            Contract::STAMP_CREATED_BY  => $this->{Contract::STAMP_CREATED_BY},
            Contract::STAMP_UPDATED     => $this->{Contract::STAMP_UPDATED},
            Contract::STAMP_UPDATED_BY  => $this->{Contract::STAMP_UPDATED_BY},
            "project"                   => $this->project->makeHidden("team"),
            "users"                     => $contractUsers,
            "contract_invites"          => $contractInvites
        ]);
    }
}

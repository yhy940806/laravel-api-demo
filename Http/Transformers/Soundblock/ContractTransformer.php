<?php

namespace App\Http\Transformers\Soundblock;

use App\Traits\StampCache;
use App\Models\{BaseModel, Soundblock\Contract};
use App\Http\Transformers\BaseTransformer;

class ContractTransformer extends BaseTransformer
{
    use StampCache;

    public function transform(Contract $objContract)
    {

        $response = [
            "contract_uuid" => $objContract->contract_uuid,
            "flag_status" => $objContract->flag_status,
            BaseModel::STAMP_CREATED => $objContract->stamp_created,
            BaseModel::STAMP_UPDATED => $objContract->stamp_updated,
        ];

        if ($objContract->pivot) {
            $response["user_payout"] = $objContract->pivot->user_payout;
        }

        return($response);
    }

    public function includeProject(Contract $objContract) {
        return $this->item($objContract->project, new ProjectTransformer());
    }

    public function includeUsers(Contract $objContract) {
        $users = $objContract->users()->wherePivot("contract_version", $objContract->contract_version)->get();

        return $this->collection($users, new ContractUserTransformer($objContract->project, ["emails", "permissionsInGroup"]));
    }

    public function includeContractInvites(Contract $objContract) {
        $invites = $objContract->contractInvites()->wherePivot("contract_version", $objContract->contract_version)->get();

        return $this->collection($invites, new InviteContractTransformer());
    }
}

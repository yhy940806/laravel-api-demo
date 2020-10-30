<?php

namespace App\Http\Transformers\Soundblock;

use League\Fractal\TransformerAbstract;
use App\Models\{Core\Auth\AuthGroup, Soundblock\Invites, User, Soundblock\Service, Soundblock\Contract};
use App\Http\Transformers\{Auth\AuthPermissionTransformer,
    User\EmailTransformer
};
use App\Traits\StampCache;

class InviteContractTransformer extends TransformerAbstract {
    use StampCache;

    public function transform(Invites $objInvite) {
        $response = [
            "invite_uuid"  => $objInvite->invite_uuid,
            "invite_email" => $objInvite->invite_email,
            "invite_name"  => $objInvite->invite_name,
            "invite_role"  => $objInvite->invite_role,
            "flag_used"    => $objInvite->flag_used,
            "contract" => ""
        ];

        if ($objInvite->pivot) {
            $response["contract"] = [
                "data" => [
                    "contract_uuid" => $objInvite->pivot->contract_uuid,
                    "user_payout"   => $objInvite->pivot->user_payout,
                ],
            ];
        }

        return $response;
    }
}

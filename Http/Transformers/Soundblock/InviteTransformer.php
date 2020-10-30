<?php

namespace App\Http\Transformers\Soundblock;

use App\Helpers\Util;
use App\Traits\StampCache;
use App\Http\Transformers\BaseTransformer;
use App\Models\Soundblock\{Contract, Project, Service, Invites};

class InviteTransformer extends BaseTransformer {
    use StampCache;

    public function transform(Invites $objInvite) {
        $arrService = [];
        /** @var Contract $objContract */
        $objContract = $objInvite->contracts()->first();
        /** @var Project $objProject */
        $objProject = $objContract->project;
        /** @var Service $objService */
        $objService = $objProject->service()->where("flag_status", "active")->first();

        if (is_object($objService)) {
            $arrService = [
                "service_uuid" => $objService->service_uuid,
                "service_name" => $objService->service_name,
            ];
        }

        return [
            "invite_uuid"  => $objInvite->invite_uuid,
            "invite_hash"  => $objInvite->invite_hash,
            "invite_email" => $objInvite->invite_email,
            "invite_name"  => $objInvite->invite_name,
            "invite_role"  => $objInvite->invite_role,
            "payout"       => $objContract->pivot->user_payout,
            "project"      => [
                "project_uuid"    => $objProject->project_uuid,
                "project_title"   => $objProject->project_title,
                "project_type"    => $objProject->project_type,
                "project_date"    => $objProject->project_date,
                "project_artwork" => Util::project_artwork_url($objProject),
            ],
            "service"      => $arrService,
        ];
    }
}

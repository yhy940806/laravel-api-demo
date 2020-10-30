<?php

namespace App\Http\Transformers\Soundblock;

use League\Fractal\TransformerAbstract;
use App\Models\{Core\Auth\AuthGroup, User,
    Soundblock\Service,
    Soundblock\Contract
};
use App\Http\Transformers\{Auth\AuthPermissionTransformer,
    User\EmailTransformer
};
use App\Traits\StampCache;

class UserContractTransformer extends TransformerAbstract
{
    use StampCache;

    protected $objContract;
    protected $objService;
    protected $objAuthGroup;
    public $availableIncludes = [];
    protected $defaultIncludes = [];

    public function __construct(?array $arrIncludes = null, ?Contract $objContract = null, ?AuthGroup $objAuthGroup = null, ?Service $objService = null)
    {

        $this->objContract = $objContract;
        $this->objAuthGroup = $objAuthGroup;
        $this->objService = $objService;
        if (isset($arrIncludes))
        {
            foreach($arrIncludes as $item)
            {
                $item = strtolower($item);
                $this->availableIncludes []= $item;
                $this->defaultIncludes []= $item;
            }
        }
    }

    public function transform(User $objUser)
    {
        $response = [
            "user_uuid" => $objUser->user_uuid,
            "name_first" => $objUser->name_first,
            "name_middle" => $objUser->name_middle,
            "name_last" => $objUser->name_last,
        ];

        if ($this->objContract)
        {
            $objContract = $objUser->contracts()->wherePivot("contract_id", $this->objContract->contract_id)->first();

            if (isset($objContract))
            {
                $response["contract"] = [
                    "data" => [
                        "contract_uuid" => $objContract->contract_uuid,
                        "user_payout" =>  $objContract->pivot->user_payout,
                    ]
                ];
            } else {
                $response["contract"] = [];
            }
        }

        if ($this->objService)
        {
            $objOwner = $this->objService->user;
            if ($objOwner->user_id == $objUser->user_id)
            {
                $response["is_owner"] = true;
            } else {
                $response["is_owner"] = false;
            }
        }

        return(array_merge($response, $this->stamp($objUser)));
    }

    public function includePermissionsInGroup(User $objUser)
    {
        if ($this->objAuthGroup)
        {
            return($this->collection($objUser->permissionsInGroup()
                        ->wherePivot("group_id", $this->objAuthGroup->group_id)
                        ->get(), new AuthPermissionTransformer));
        } else {
            return($this->collection($objUser->permissionsInGroup, new AuthPermissionTransformer));
        }

    }

    public function includeEmails(User $objUser)
    {
        return($this->item($objUser->emails()->where("flag_primary", true)->first(), new EmailTransformer));
    }
}

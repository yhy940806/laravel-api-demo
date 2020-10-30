<?php

namespace App\Http\Transformers\Soundblock;

use App\Traits\StampCache;
use App\Models\Soundblock\Team;
use App\Http\Transformers\BaseTransformer;
use App\Http\Transformers\User\UserTransformer;

class TeamTransformer extends BaseTransformer
{
    use StampCache;

    /**
     * @return array
     */
    public function transform(Team $objTeam)
    {
        $response = [
            "team_uuid" => $objTeam->team_uuid,
        ];

        return(array_merge($response, $this->stamp($objTeam)));
    }

    public function includeProject(Team $objTeam)
    {
        return($this->item($objTeam->project, new ProjectTransformer));
    }

    public function includeUsers(Team $objTeam)
    {
        return($this->collection($objTeam->users, new UserTransformer));
    }
}

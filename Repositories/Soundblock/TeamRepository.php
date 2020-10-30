<?php

namespace App\Repositories\Soundblock;

use Util;
use Auth;
use Constant;
use App\Models\{
    BaseModel,
    User,
    Soundblock\Team
};
use App\Repositories\BaseRepository;

class TeamRepository extends BaseRepository {
    /**
     * @param Team $team
     * @return void
     */
    public function __construct(Team $team) {
        $this->model = $team;
    }

    /**
     * @param Team $team
     * @param \Illuminate\Database\Eloquent\Collection $arrInfo
     */
    public function addMembers(Team $team, $arrInfo) {
        foreach ($arrInfo as $info) {
            /** @var User $user */
            $user = $info["email"]->user;
            $intVal = $this->memberExists($team, $user);
            switch ($intVal) {
                case Constant::NOT_EXIST:
                {
                    $team->users()->attach($user->user_id, [
                        "row_uuid"                  => Util::uuid(),
                        "user_uuid"                 => $user->user_uuid,
                        "team_uuid"                 => $team->team_uuid,
                        "user_payout"               => isset($info["user_payout"]) ? $info["user_payout"] : null,
                        "user_role"                 => Util::ucLabel($info["user_role"]),
                        BaseModel::STAMP_CREATED    => time(),
                        BaseModel::STAMP_CREATED_BY => Auth::id(),
                        BaseModel::STAMP_UPDATED    => time(),
                        BaseModel::STAMP_UPDATED_BY => Auth::id(),
                    ]);
                }

                case Constant::EXIST:
                {
                    $team->users()->updateExistingPivot($user->user_id, [
                        "user_payout"               => isset($info["user_payout"]) ? $info["user_payout"] : null,
                        "user_role"                 => Util::ucLabel($info["user_role"]),
                        BaseModel::STAMP_UPDATED    => time(),
                        BaseModel::STAMP_UPDATED_BY => Auth::id(),
                        BaseModel::DELETED_AT       => null,
                        BaseModel::STAMP_DELETED    => null,
                        BaseModel::STAMP_DELETED_BY => null,
                    ]);
                }
            }
        }
        return ($team);
    }

    /**
     * @param Team $team
     * @param User $user
     * @return int
     */
    protected function memberExists(Team $team, User $user): int {
        $bnExists = $team->usersWithTrashed()
                         ->wherePivot("user_id", $user->user_id)
                         ->exists();
        if ($bnExists) {
            return (Constant::EXIST);
        } else {
            return (Constant::NOT_EXIST);
        }
    }

    /**
     * @param Team $team
     * @param User $user
     * @param array $option
     *          $option = [
     *              'user_role'      => (string) user role in the team require.
     *              'user_payout'    => (integer) user payout
     *          ]
     */
    public function addMember(Team $team, User $user, array $option) {
        $isExist = $this->memberExists($team, $user);
        switch ($isExist) {
            case Constant::EXIST:
            {
                $team->users()->updateExistingPivot($user->user_id, [
                    "user_payout"               => isset($option["user_payout"]) ? $option["user_payout"] : null,
                    "user_role"                 => Util::ucLabel($option["user_role"]),
                    BaseModel::STAMP_UPDATED    => time(),
                    BaseModel::STAMP_UPDATED_BY => Auth::id(),
                    BaseModel::DELETED_AT       => null,
                    BaseModel::STAMP_DELETED    => null,
                    BaseModel::STAMP_DELETED_BY => null,
                ]);
                break;
            }
            case Constant::NOT_EXIST:
            {
                $team->users()->attach($user->user_id, [
                    "row_uuid"                  => Util::uuid(),
                    "user_uuid"                 => $user->user_uuid,
                    "team_uuid"                 => $team->team_uuid,
                    "user_payout"               => isset($option["user_payout"]) ? $option["user_payout"] : null,
                    "user_role"                 => Util::ucLabel($option["user_role"]),
                    BaseModel::STAMP_CREATED    => time(),
                    BaseModel::STAMP_CREATED_BY => Auth::id(),
                    BaseModel::STAMP_UPDATED    => time(),
                    BaseModel::STAMP_UPDATED_BY => Auth::id(),
                    BaseModel::DELETED_AT       => null,
                    BaseModel::STAMP_DELETED    => null,
                    BaseModel::STAMP_DELETED_BY => null,
                ]);
                break;
            }
        }
    }

    /**
     * @param Team $team
     * @param \Illuminate\Database\Eloquent\Collection $users
     *
     * @return int
     */
    public function removeMembers(Team $team, $users) {
        return $team->users()->newPivotStatement()
                    ->where("team_id", $team->team_id)
                    ->whereIn("user_id", $users->pluck("user_id")->toArray())
                    ->update([
                        BaseModel::DELETED_AT       => now(),
                        BaseModel::STAMP_DELETED    => time(),
                        BaseModel::STAMP_DELETED_BY => Auth::id(),
                    ]);
    }

    /**
     * @param Team $team
     * @param \Illuminate\Database\Eloquent\Collection $users
     *
     * @return int
     */
    public function initializeMembers(Team $team, $users) {
        return $team->users()->newPivotStatement()
                    ->where("team_id", $team->team_id)
                    ->whereNotIn("user_id", $users->pluck("user_id")->toArray())
                    ->update([
                        BaseModel::DELETED_AT       => now(),
                        BaseModel::STAMP_DELETED    => time(),
                        BaseModel::STAMP_DELETED_BY => Auth::id(),
                    ]);
    }
}

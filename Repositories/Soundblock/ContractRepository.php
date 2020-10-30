<?php

namespace App\Repositories\Soundblock;

use Util;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\{Soundblock\Contract, Soundblock\Project, User};

class ContractRepository extends BaseRepository {
    /**
     * ContractRepository constructor.
     * @param Contract $objContract
     */
    public function __construct(Contract $objContract) {
        $this->model = $objContract;
    }

    /**
     * @param Project $objProject
     * @return mixed
     */
    public function findByProject(Project $objProject) {
        return ($objProject->contracts);
    }

    /**
     * @param Project $objProject
     * @param bool $blFail
     * @return Model|Contract
     */
    public function findLatestByProject(Project $objProject, bool $blFail = true): ?Contract {
        $objBuilder = $objProject->contracts()->whereNull(Contract::STAMP_ENDS)
            ->orderBy(Contract::STAMP_BEGINS, "desc");

        if($blFail) {
            return $objBuilder->firstOrFail();
        }

        return $objBuilder->first();
    }

    /**
     * @param Contract $contract
     * @param User $user
     * @return \Illuminate\Support\HigherOrderCollectionProxy|mixed
     * @throws \Exception
     */
    public function getContractUserDetails(Contract $contract, User $user) {
        $intCurrentCycle = $this->getCurrentCycle($contract);
        $objUser = $contract->users()->wherePivot("contract_version", $intCurrentCycle)->find($user->user_id);

        if(is_null($objUser)) {
            throw new \Exception("This user is not contract member.", 401);
        }

        return $objUser->pivot;
    }

    /**
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function checkUserInProjectContracts(User $user, Project $project) {
        return $user->contracts()->whereHas("project", function (Builder $query) use ($project) {
            $query->where("project_id", $project->project_id);
        })->exists();
    }

    /**
     * @param Contract $contract
     * @param User $user
     * @param string $contractStatus
     * @return int
     */
    public function updateContractUser(Contract $contract, User $user, string $contractStatus) {
        $intCurrentCycle = $this->getCurrentCycle($contract);

        return $contract->users()->wherePivot("contract_version", $intCurrentCycle)->updateExistingPivot($user->user_id, [
            "contract_status" => $contractStatus,
        ]);
    }

    /**
     * @param Contract $contract
     * @return bool
     */
    public function hasNotAcceptedUsers(Contract $contract): bool {
        $intCurrentCycle = $this->getCurrentCycle($contract);

        return $contract->users()->wherePivot("contract_status", "!=", "Accepted")
            ->wherePivot("contract_version", $intCurrentCycle)->exists();
    }

    public function checkUserExist(Contract $contract, User $user) {
        return $contract->whereHas("users", function (Builder $query) use ($user) {
            $query->where("users.user_id", $user->user_id);
        })->exists();
    }

    public function getCurrentCycle(Contract $contract) {
        return $contract->users()->max("contract_version");
    }

    public function getActiveHistory(Contract $contract) {
        return $contract->history()->where("history_event", "active")->latest()->first();
    }

    public function getCurrentCycleUsers(Contract $contract) {
        $intCurrentCycle = $this->getCurrentCycle($contract);

        return $contract->users()->wherePivot("contract_version", $intCurrentCycle)->get();
    }

    public function createContractHistory(Contract $contract, User $user, string $historyEvent) {
        return $contract->history()->create([
            "row_uuid"       => \Util::uuid(),
            "user_id"        => $user->user_id,
            "user_uuid"      => $user->user_uuid,
            "contract_uuid"  => $contract->contract_uuid,
            "contract_state" => $contract->makeVisible(["contract_id", "service_id", "project_id"])
                ->makeHidden("users")->toArray(),
            "history_event"  => $historyEvent,
        ]);
    }

    public function createContractUsersHistory(Contract $contract, User $user, string $historyEvent) {
        return $contract->usersHistory()->create([
            "row_uuid"             => \Util::uuid(),
            "user_id"              => $user->user_id,
            "user_uuid"            => $user->user_uuid,
            "contract_uuid"        => $contract->contract_uuid,
            "contract_users_state" => $contract->users()->withPivot("user_payout")->get()
                ->makeVisible(["user_id", "pivot"])->toArray(),
            "history_event"        => $historyEvent,
            "contract_version"           => $contract->contract_version,
        ]);
    }

    /**
     * @param User $objUser
     * @param Project $objProject
     * @param array $arrContractStatus
     *
     * @return null|Contract
     */
    public function findByUserAndStatus(User $objUser, Project $objProject, array $arrContractStatus = []): ?Contract {
        $objLatestContract = $this->findLatestByProject($objProject, false);
        if (!$objLatestContract)
            return(null);
        /** @var \Illuminate\Database\Query\Builder */
        $queryBuilder = $objUser->contracts()->wherePivot("contract_id", $objLatestContract->contract_id)
                                ->wherePivot("contract_version", $objLatestContract->contract_version)->first();
        if (!empty($arrContractStatus)) {
            $arrContractStatus = array_map([Util::class, "ucLabel"], $arrContractStatus);
            $queryBuilder->wherePivotIn("contract_status", $arrContractStatus);
        }

        return($queryBuilder->first()->load(["service"])->makeHidden(["service_uuid"]));
    }

    /**
     * @param Contract $contract
     *
     * @return bool
     */
    public function canModify(Contract $contract)
    {
        return(strtolower($contract->flag_status) != "modifying");
    }
}

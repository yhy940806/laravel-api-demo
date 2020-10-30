<?php


namespace App\Contracts\Soundblock\Contracts;

use App\Models\User;
use App\Models\Soundblock\{
    Contract,
    Project,
    Service
};
use Illuminate\Database\Eloquent\Collection;

interface SmartContractsContract {
    public function create(Project $objProject, Service $service, array $arrParams) : Contract;
    public function find(string $id, bool $bbFail = true) : ?Contract;
    public function update(Contract $objContract, array $arrParams) : Contract;
    public function findLatest(Project $project, bool $bbFail = true) : ?Contract;

    public function acceptContract(Contract $contract, User $user) : Contract;
    public function rejectContract(Contract $contract, User $user) : Contract;

    public function checkAccess(Contract $contract, User $user) : bool;

    public function getContractInfo(Contract $objContract): Contract;
    public function getLatestByProjectsAndStatus($arrProjects, array $arrStatus): Collection;

    public function canModify(Contract $contract): bool;
}

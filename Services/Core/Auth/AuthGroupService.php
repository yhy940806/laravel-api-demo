<?php

namespace App\Services\Core\Auth;

use Auth;
use Util;
use Client;
use Exception;
use App\Models\{Core\Auth\AuthGroup,
    Core\Auth\AuthPermission,
    BaseModel,
    Core\App,
    User,
    Soundblock\Project,
    Soundblock\Service,
    Soundblock\Collection as SoundblockCollection};
use Illuminate\Support\Collection;
use App\Repositories\{Core\Auth\AuthGroupRepository,
    Soundblock\CollectionRepository,
    Soundblock\ContractRepository,
    Soundblock\ProjectRepository,
    User\UserRepository};

class AuthGroupService
{
    protected UserRepository $userRepo;
    protected AuthGroupRepository $groupRepo;
    protected ProjectRepository $projectRepo;
    protected CollectionRepository $colRepo;

    // protected $authService;

    const EXISTS = 1;
    const SOFT_DELETED = 2;
    const NOT_EXISTS = 0;
    /**
     * @var ContractRepository
     */
    private $contractRepository;

    /**
     * AuthGroupService constructor.
     * @param AuthGroupRepository $groupRepo
     * @param UserRepository $userRepo
     * @param ProjectRepository $projectRepo
     * @param CollectionRepository $colRepo
     * @param ContractRepository $contractRepository
     */
    public function __construct(AuthGroupRepository $groupRepo, UserRepository $userRepo, ProjectRepository $projectRepo,
                                CollectionRepository $colRepo, ContractRepository $contractRepository)
    {
        $this->groupRepo = $groupRepo;
        $this->userRepo = $userRepo;
        $this->projectRepo = $projectRepo;
        $this->colRepo = $colRepo;
        $this->contractRepository = $contractRepository;
    }

    /**
     * @param array $arrParams
     * @param bool $bnFlagCritical
     * @return AuthGroup
     * @property
     * auth_name,
     * group_name
     * group_users(user_uuid array)
     */
    public function create(array $arrParams, $bnFlagCritical = false) : AuthGroup {
        $arrGroup = array();

        $objAuth = Client::auth();
        $arrGroup["auth_id"] = $objAuth->auth_id;
        $arrGroup["auth_uuid"] = $objAuth->auth_uuid;
        $arrGroup["group_name"] = $arrParams["group_name"];
        $arrGroup["group_memo"] = $arrParams["group_memo"];
        $arrGroup["flag_critical"] = $bnFlagCritical;

        return($this->groupRepo->create($arrGroup));
    }

    /**
     * @param int $perPage
     * @return mixed
     */
    public function findAll($perPage = 10) {
        return($this->groupRepo->paginated($perPage));
    }

    /**
     * @param $id
     * @param bool $bnFailure
     * @return AuthGroup
     */
    public function find($id, bool $bnFailure = true) : AuthGroup {
        return($this->groupRepo->find($id, $bnFailure));
    }

    /**
     * @param string $name
     * @return AuthGroup
     */
    public function findByName(string $name) {
        return($this->groupRepo->findByName($name));
    }

    /**
     * @param string $userUuid
     * @param int $perPage
     * @param bool $paginate
     * @return mixed
     */
    public function findByUser(string $userUuid, int $perPage = 10, bool $paginate = true) {
        return $this->groupRepo->findByUser($userUuid, $perPage, $paginate);
    }

    /**
     * @param AuthPermission $objAuthPerm
     * @param int $perPage
     * @return mixed
     */
    public function findAllByPermission(AuthPermission $objAuthPerm, $perPage = 10) {
        return($this->groupRepo->findAllByPermission($objAuthPerm, $perPage));
    }

    /**
     * @param $arrParams
     * @param bool $blnFlagCritical
     * @return AuthGroup
     */
    public function createGroup($arrParams, $blnFlagCritical = false) {
        $objApp = Client::app();
        $objAuth = Client::auth();

        $arrUsers = collect();
        if (isset($arrParams["users"]) && $arrParams["users"] instanceof User) {
            $arrUsers->push($arrParams["user"]);
        } else {
            $arrUsers->push(Auth::user());
        }

        if ($blnFlagCritical) {
            // Will be able to check if group_type is project or service.
            $arrParams["group_name"] = Util::makeGroupName($objAuth, $arrParams["group_type"], $arrParams["object"]);
            $arrParams["group_memo"] = Util::makeGroupMemo($objAuth, $arrParams["group_type"], $arrParams["object"]);

            $objAuthGroup = $this->create($arrParams, $blnFlagCritical);
            $objAuthGroup = $this->addUsersToGroup($arrUsers, $objAuthGroup, $objApp);
        } else {
            $objAuthGroup = $this->create($arrParams, $blnFlagCritical);
            $objAuthGroup = $this->addUsersToGroup($arrUsers, $objAuthGroup, $objApp);
        }

        return($objAuthGroup);
    }

    /**
     * Add users to the group
     * @param $arrParams
     * @return AuthGroup
     */
    public function addUsers($arrParams) {
        $objApp = Client::app();
        $objAuthGroup = $this->find($arrParams["group"]);
        $arrObjUsers = $this->userRepo->findAllWhere($arrParams["users"]);

        return($this->addUsersToGroup($arrObjUsers, $objAuthGroup, $objApp));
    }

    public function remove($arrAuthGroup) {
        $objAuthGroup = $this->find($arrAuthGroup["group"]);

        if (!$objAuthGroup->flag_critical && $objAuthGroup->delete()) {
            $objAuthGroup->users()->newPivotStatement()
                        ->where("group_id", $objAuthGroup->group_id)
                        ->update([
                            BaseModel::DELETED_AT => Util::now(),
                            BaseModel::STAMP_DELETED => time(),
                            BaseModel::STAMP_DELETED_BY => Auth::id()
                ]);

            $objAuthGroup->pusers()->newPivotStatement()
                        ->where("group_id", $objAuthGroup->group_id)
                        ->update([
                            BaseModel::DELETED_AT => Util::now(),
                            BaseModel::STAMP_DELETED => time(),
                            BaseModel::STAMP_DELETED_BY => Auth::id()
                        ]);
            $objAuthGroup->permissions()->newPivotStatement()
                        ->where("group_id", $objAuthGroup->group_id)
                        ->update([
                            BaseModel::DELETED_AT => Util::now(),
                            BaseModel::STAMP_DELETED => time(),
                            BaseModel::STAMP_DELETED_BY => Auth::id()
                        ]);

            return(true);
        } else
            return(false);

    }

    /**
     * Add users to group
     * @param Collection $arrObjUsers
     * @param AuthGroup $objAuthGroup
     * @param App $objApp
     * @return AuthGroup
     */
    public function addUsersToGroup(Collection $arrObjUsers, AuthGroup $objAuthGroup, App $objApp) {
        foreach($arrObjUsers as $objUser) {
            $objAuthGroup = $this->addUserToGroup($objUser, $objAuthGroup, $objApp);
        }

        return($objAuthGroup);
    }

    /**
     * @param User $objUser
     * @param AuthGroup $objAuthGroup
     * @param App $objApp
     * @return AuthGroup
     */
    public function addUserToGroup(User $objUser, AuthGroup $objAuthGroup, App $objApp) {
        return ($this->groupRepo->addUserToGroup($objUser, $objAuthGroup, $objApp));
    }

    /**
     * Soft delete users from the group.
     * @param array $arrParams
     * @return AuthGroup
     */
    public function removeUsersFromGroup($arrParams) {
        $objAuthGroup = $this->find($arrParams["group"]);
        $arrObjUsers = $this->userRepo->findAllWhere($arrParams["users"]);

        return($this->detachUsersFromGroup($arrObjUsers, $objAuthGroup));
    }

    /**
     * @param User $objUser
     * @param AuthGroup $objAuthGroup
     * @param App $objApp
     * @return AuthGroup
     */
    public function attachUserToGroup(User $objUser, AuthGroup $objAuthGroup, App $objApp) {
        return ($this->groupRepo->attachUserToGroup($objUser, $objAuthGroup, $objApp));
    }

    public function attachUserToGroupWithPerm(User $objUser, AuthGroup $objAuthGroup, AuthPermission $objAuthPerm)
    {
        $objUser->groupsWithPermissions()->attach($objAuthGroup->group_id, [
            "row_uuid" => Util::uuid(),
            "user_uuid" => $objUser->user_uuid,
            "group_uuid" => $objAuthGroup->group_uuid,
            "permission_id" => $objAuthPerm->permission_id,
            "permission_uuid" => $objAuthPerm->permission_uuid,
            "permission_value" => 1,
            BaseModel::STAMP_CREATED => Util::now(),
            BaseModel::STAMP_CREATED_BY => Auth::id(),
            BaseModel::STAMP_UPDATED => Util::now(),
            BaseModel::STAMP_UPDATED_BY => Auth::id(),
        ]);

        return($objUser);
    }

    public function attachUserInGroupWithPerm(User $objUser, AuthGroup $objAuthGroup, AuthPermission $objAuthPerm)
    {
        $objUser->groupsWithPermissions()->wherePivot("permission_id", $objAuthPerm->permission_id)
            ->updateExistingPivot($objAuthGroup->group_id, [
                "permission_value" => 1,
                BaseModel::UPDATED_AT => Util::now(),
                BaseModel::STAMP_UPDATED => time(),
                BaseModel::STAMP_UPDATED_BY => Auth::id()
        ]);

        return($objUser);
    }

    /**
     * Detach users from pivot (auth_groups_users)
     * @param $arrObjUsers
     * @param $objAuthGroup
     *
     * @return AuthGroup
     */

    public function detachUsersFromGroup(Collection $arrObjUsers, AuthGroup $objAuthGroup)
    {
        return ($this->groupRepo->detachUsersFromGroup($arrObjUsers, $objAuthGroup));
    }

    /**
     * @param mixed $collection
     * @return AuthGroup
     */
    public function findByCollection($collection) : AuthGroup
    {
        if ($collection instanceof SoundblockCollection)
        {
            $objCol = $collection;
        } else if (Util::is_uuid($collection) || is_int($collection)) {
            $objCol = $this->colRepo->find($collection, true);
        } else {
            throw new Exception();
        }

        return($this->findByProject($objCol->project));
    }

    /**
     * @param mixed $project
     * @return AuthGroup
     */
    public function findByProject($project) : AuthGroup
    {
        if ($project instanceof Project)
        {
            $objProject = $project;
        } else if (Util::is_uuid($project) || is_int($project)) {
            $objProject = $this->projectRepo->find($project, true);
        } else {
            throw new Exception("Project is invalid parameter.");
        }

        return($this->groupRepo->findByProject($objProject));
    }

    /**
     * @param Service $objService
     * @return AuthGroup
     */
    public function findByService(Service $objService)
    {
        return($this->groupRepo->findByService($objService));
    }

    /**
     * Check if the user exists in the group already.
     * @param User $objUser
     * @param AuthGroup $objAuthGroup
     *
     * @return int
     */
    public function checkIfUserExists(User $objUser, AuthGroup $objAuthGroup)
    {
        return($this->groupRepo->checkIfUserExists($objUser, $objAuthGroup));
    }


    public function update(AuthGroup $objAuthGroup, array $arrParams, $bnFlagCritical = false) : AuthGroup
    {

        $arrGroup = array();
        if (isset($arrParams["group_name"]))
            $arrGroup["group_name"] = $arrParams["group_name"];

        if (isset($arrParams["group_memo"]))
            $arrGroup["group_memo"] = $arrParams["group_memo"];

        $arrGroup["flag_critical"] = $bnFlagCritical;

        return($this->groupRepo->update($objAuthGroup, $arrGroup));
    }


    /**
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public function findAllWhere(array $where, string $field = "uuid") {
        return $this->groupRepo->findAllWhere($where, $field);
    }

    public function search(array $arrParams) {
        return $this->groupRepo->search($arrParams);
    }
}

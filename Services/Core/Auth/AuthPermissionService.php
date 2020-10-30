<?php

namespace App\Services\Core\Auth;

use Util;
use Auth;
use Constant;
use Exception;
use App\Models\{
    Core\Auth\AuthGroup,
    Core\Auth\AuthPermission,
    BaseModel,
    Soundblock\Service,
    Soundblock\Project,
    User
};
use App\Exceptions\AuthException;
use App\Repositories\{User\UserRepository, Core\Auth\AuthGroupRepository, Core\Auth\AuthPermissionRepository};
use Illuminate\Database\Eloquent\Collection as SupportCollection;

class AuthPermissionService {
    /** @var AuthGroupRepository */
    protected $groupRepo;
    /** @var UserRepository */
    protected $userRepo;
    /** @var AuthPermissionRepository */
    protected $permRepo;

    /**
     * @param AuthGroupRepository $groupRepo
     * @param AuthPermissionRepository $permRepo
     * @param UserRepository $userRepo
     * @return void
     */
    public function __construct(AuthGroupRepository $groupRepo, AuthPermissionRepository $permRepo, UserRepository $userRepo) {
        $this->groupRepo = $groupRepo;
        $this->permRepo = $permRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * @param array $arrParams
     * @param bool $bnFlagCritical
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $arrParams, bool $bnFlagCritical = false): \Illuminate\Database\Eloquent\Model {
        $arrPerm = [];

        $arrPerm["permission_name"] = $arrParams["permission_name"];
        $arrPerm["permission_memo"] = $arrParams["permission_memo"];
        $arrPerm["flag_critical"] = $bnFlagCritical;

        return ($this->permRepo->create($arrPerm));
    }

    /**
     * @param AuthPermission $objAuthPerm
     * @param array $arrParams
     * @return AuthPermission
     */
    public function update(AuthPermission $objAuthPerm, array $arrParams): AuthPermission {
        $arrPerm = [];

        if (isset($arrParams["name"])) {
            $arrPerm["permission_name"] = $arrParams["name"];
        }

        if (isset($arrParams["memo"])) {
            $arrPerm["permission_memo"] = $arrParams["memo"];
        }

        if (isset($arrParams["critical"])) {
            $arrPerm["flag_critical"] = $arrParams["critical"];
        }

        return ($this->permRepo->update($objAuthPerm, $arrPerm));
    }

    /**
     * @param mixed $name
     * @return
     */
    public function findAllByName($name) {
        return ($this->permRepo->findAllByName($name));
    }

    /**
     * @param int $perPage
     * @return
     */
    public function findAll(int $perPage = 10) {
        return (AuthPermission::paginate($perPage));
    }

    /**
     * @param Service $service
     * @return SupportCollection
     */
    public function userPermissions(Service $service, ?User $user = null): SupportCollection {
        if (is_null($user)) {
            /** @var User $user */
            $user = Auth::user();
        }
        $authGroup = $this->groupRepo->findByService($service);

        return ($this->findAllByGroupAndUser($authGroup, $user));
    }

    /**
     * @param AuthGroup $authGroup
     * @param User $user
     * @return SupportCollection
     */
    public function findAllByGroupAndUser(AuthGroup $authGroup, ?User $user = null) {
        if (is_null($user)) {
            /** @var User $user */
            $user = Auth::user();
        }
        /** @var SupportCollection */
        $groupPermissions = $this->permRepo->findAllByGroup($authGroup);
        /** @var SupportCollection */
        $userPermissiosns = $this->permRepo->findAllByUserAndGroup($user, $authGroup);
        $groupPermissions = $groupPermissions->reject(function ($value) use ($userPermissiosns) {
            foreach ($userPermissiosns as $permission) {
                if ($permission->permission_id == $value->permission_id)
                    return (true);
            }
        });
        $groupPermissions = $groupPermissions->merge($userPermissiosns);

        return ($groupPermissions);
    }

    public function detachByPermission(AuthPermission $objAuthPerm) {
        $objAuthPerm->groups()->newPivotStatement()
                    ->where("permission_id", $objAuthPerm->permission_id)
                    ->update([
                        BaseModel::DELETED_AT       => Util::now(),
                        BaseModel::STAMP_DELETED    => time(),
                        BaseModel::STAMP_DELETED_BY => Auth::id(),
                    ]);

        $objAuthPerm->pgroups()->newPivotStatement()
                    ->where("permission_id", $objAuthPerm->permission_id)
                    ->update([
                        BaseModel::DELETED_AT       => Util::now(),
                        BaseModel::STAMP_DELETED    => time(),
                        BaseModel::STAMP_DELETED_BY => Auth::id(),
                    ]);

        $objAuthPerm->pusers()->newPivotStatement()
                    ->where("permission_id", $objAuthPerm->permission_id)
                    ->update([
                        BaseModel::DELETED_AT       => Util::now(),
                        BaseModel::STAMP_DELETED    => time(),
                        BaseModel::STAMP_DELETED_BY => Auth::id(),
                    ]);
    }

    /**
     * Add permissions to group
     * @param array $arrParams
     * @return AuthGroup
     */
    public function addGroupPermissions(array $arrParams): AuthGroup {
        $objAuthGroup = $this->authGroupService->find($arrParams["group"]);
        $arrObjPerms = $this->findAllWhere($arrParams["permissions"]);

        return ($this->attachGroupPermissions($arrObjPerms, $objAuthGroup));
    }

    public function findAllWhere(array $where, $field = "uuid") {
        return ($this->permRepo->findAllWhere($where, $field));
    }

    /**
     * @param SupportCollection $arrObjPerms
     * @param AuthGroup $objAuthGroup
     * @param int $intValue
     *
     * @return AuthGroup
     */
    public function attachGroupPermissions(SupportCollection $arrObjPerms, AuthGroup $objAuthGroup, int $intValue = 1): AuthGroup {
        foreach ($arrObjPerms as $objAuthPerm) {
            switch ($this->checkIfExistsPermission($objAuthPerm, $objAuthGroup)) {
                case Constant::EXIST :
                {
                    $objAuthGroup->permissions()->updateExistingPivot($objAuthPerm->permission_id, [
                        BaseModel::UPDATED_AT       => Util::now(),
                        BaseModel::STAMP_UPDATED    => time(),
                        BaseModel::STAMP_UPDATED_BY => Auth::id(),
                        "permission_value"          => $intValue,
                    ]);
                    break;
                }

                case Constant::NOT_EXIST :
                {
                    $objAuthGroup->permissions()->attach($objAuthPerm->permission_id, [
                        "row_uuid"                  => Util::uuid(),
                        "group_uuid"                => $objAuthGroup->group_uuid,
                        "permission_uuid"           => $objAuthPerm->permission_uuid,
                        "permission_value"          => $intValue,
                        BaseModel::STAMP_CREATED    => time(),
                        BaseModel::STAMP_CREATED_BY => Auth::id(),
                        BaseModel::STAMP_UPDATED    => time(),
                        BaseModel::STAMP_UPDATED_BY => Auth::id(),
                    ]);
                    break;
                }
            }
        }

        return ($objAuthGroup);
    }

    /**
     * @param AuthPermission $objAuthPerm
     * @param AuthGroup $objAuthGroup
     * @param User $objUser
     * @param int $intValue
     * @return bool
     */
    public function checkIfExistsPermission(AuthPermission $objAuthPerm, AuthGroup $objAuthGroup, ?User $objUser = null): bool {
        if (isset($objUser)) {
            // Check if this permission is group level permission.
            $existAtGroupLevel = $objAuthGroup->permissions()->wherePivot("permission_id", $objAuthPerm->permission_id)
                                              ->wherePivot("permission_value", 1)->exists();
            $exist = $objUser->groupsWithPermissionsWithTrashed()
                             ->wherePivot("group_id", $objAuthGroup->group_id)
                             ->wherePivot("permission_id", $objAuthPerm->permission_id)->exists();
            if ($exist || $existAtGroupLevel) {
                return (Constant::EXIST);
            } else {
                return (Constant::NOT_EXIST);
            }
        } else {
            $query = $objAuthGroup->join("core_auth_permissions_groups", "core_auth_groups.group_id", "=", "core_auth_permissions_groups.group_id")
                                  ->where("core_auth_permissions_groups.permission_id", $objAuthPerm->permission_id)
                                  ->where("core_auth_groups.group_id", $objAuthGroup->group_id);

            $softDeleteQuery = clone $query;
            $result = $softDeleteQuery->where("core_auth_permissions_groups." . BaseModel::STAMP_DELETED, "<>", null)
                                      ->exists();

            if ($result) {
                return (Constant::SOFT_DELETED);
            } else {
                $result = $query->where("core_auth_permissions_groups." . BaseModel::STAMP_DELETED)->exists();

                if ($result) {
                    return (Constant::EXIST);
                }

                return (Constant::NOT_EXIST);
            }
        }
    }

    /**
     * @param array $arrParams
     * @return User
     */
    public function addUserPermissions(array $arrParams): User {
        $arrAuthPerms = $this->findAllWhere($arrParams["permissions"]);
        $objUser = $this->userService->find($arrParams["user"]);
        $objAuthGroup = $this->authGroupService->find($arrParams["group"]);

        if ($this->authGroupService->checkIfUserExists($objUser, $objAuthGroup) === Constant::EXIST) {
            return ($this->attachUserPermissions($arrAuthPerms, $objUser, $objAuthGroup));
        } else {
            throw new Exception();
        }
    }

    /**
     * @param SupportCollection $arrObjAuthPerms
     * @param User $objUser
     * @param AuthGroup $objAuthGroup
     * @param int $intValue
     * @return User
     */
    public function attachUserPermissions(SupportCollection $arrObjAuthPerms, User $objUser, AuthGroup $objAuthGroup, int $intValue = 1): User {

        foreach ($arrObjAuthPerms as $objAuthPerm) {
            switch ($this->checkIfExistsPermission($objAuthPerm, $objAuthGroup, $objUser)) {
                case Constant::EXIST:
                {
                    $objUser->permissionsInGroup()->wherePivot("group_id", $objAuthGroup->group_id)
                            ->updateExistingPivot($objAuthPerm->permission_id, [
                                "permission_value"          => $intValue,
                                BaseModel::STAMP_UPDATED    => time(),
                                BaseModel::STAMP_UPDATED_BY => Auth::id(),
                                BaseModel::STAMP_DELETED    => null,
                                BaseModel::DELETED_AT       => null,
                                BaseModel::STAMP_DELETED_BY => null,
                            ]);
                    break;
                }

                case Constant::NOT_EXIST:
                {
                    $objUser->permissionsInGroup()->attach($objAuthPerm->permission_id, [
                        "row_uuid"                  => Util::uuid(),
                        "group_id"                  => $objAuthGroup->group_id,
                        "group_uuid"                => $objAuthGroup->group_uuid,
                        "user_uuid"                 => $objUser->user_uuid,
                        "permission_uuid"           => $objAuthPerm->permission_uuid,
                        "permission_value"          => $intValue,
                        BaseModel::STAMP_CREATED    => time(),
                        BaseModel::STAMP_CREATED_BY => Auth::id(),
                        BaseModel::STAMP_UPDATED    => time(),
                        BaseModel::STAMP_UPDATED_BY => Auth::id(),
                    ]);
                    break;
                }
            }

        }

        return ($objUser);
    }

    /**
     * @param array $arrParams
     * @return User
     */
    public function deleteGroupPermission($arrParams) {
        $objAuthGroup = $this->groupRepo->find($arrParams["group"]);
        $objAuthPerm = $this->find($arrParams["permission"]);

        return ($this->detachGroupPermission($objAuthPerm, $objAuthGroup));
    }

    /**
     * @param mixed $id
     * @param bool $bnFailure
     * @return AuthPermission
     */
    public function find($id, ?bool $bnFailure = true): AuthPermission {
        return ($this->permRepo->find($id, $bnFailure));
    }

    /**
     * @param AuthPermission $objAuthPerm
     * @param AuthGroup $objAuthGroup
     * @return AuthGroup
     */
    public function detachGroupPermission(AuthPermission $objAuthPerm, AuthGroup $objAuthGroup): AuthGroup {
        $objAuthGroup->permissions()->updateExistingPivot($objAuthPerm->permission_id, [
            BaseModel::DELETED_AT       => Util::now(),
            BaseModel::STAMP_DELETED    => time(),
            BaseModel::STAMP_DELETED_BY => Auth::id(),
            BaseModel::UPDATED_AT       => Util::now(),
            BaseModel::STAMP_UPDATED    => time(),
            BaseModel::STAMP_UPDATED_BY => Auth::id(),
        ]);

        return ($objAuthGroup);
    }

    /**
     * @param array $arrParams
     * @return User
     */
    public function deleteUserPermission(array $arrParams): User {
        $objAuthPerm = $this->find($arrParams["permission"]);
        $objUser = $this->userRepo->find($arrParams["user"]);
        $objAuthGroup = $this->groupRepo->find($arrParams["group"]);

        if ($this->checkIfExistsPermission($objAuthPerm, $objAuthGroup, $objUser) !== static::NOT_EXISTS) {
            return ($this->detachUserPermission($objAuthPerm, $objUser, $objAuthGroup));
        } else {
            throw new  Exception();
        }
    }

    /**
     * @param AuthPermission $objAuthPerm
     * @param User $objUser
     * @param AuthGroup $objAuthGroup
     * @return User
     */
    public function detachUserPermission(AuthPermission $objAuthPerm, User $objUser, AuthGroup $objAuthGroup): User {
        $objUser->permissionsInGroup()->wherePivot("group_id", $objAuthGroup->group_id)
                ->updateExistingPivot($objAuthPerm->permission_id, [
                    BaseModel::DELETED_AT       => Util::now(),
                    BaseModel::STAMP_DELETED    => time(),
                    BaseModel::STAMP_DELETED_BY => Auth::id(),
                    BaseModel::UPDATED_AT       => Util::now(),
                    BaseModel::STAMP_UPDATED    => time(),
                    BaseModel::STAMP_UPDATED_BY => Auth::id(),
                ]);

        return ($objUser);
    }

    /**
     * @param int $intValue
     * @param AuthPermission $objAuthPerm
     * @param AuthGroup $objAuthGroup
     * @return AuthGroup
     */
    public function updateGroupPermission(int $intValue, AuthPermission $objAuthPerm, AuthGroup $objAuthGroup): AuthGroup {
        if ($intValue == 0 || $intValue == 1) {
            if ($this->checkIfExistsPermission($objAuthPerm, $objAuthGroup) != static::NOT_EXISTS) {
                $objAuthGroup->permissions()->updateExistingPivot($objAuthPerm->permission_id, [
                    "permission_value"          => $intValue,
                    BaseModel::STAMP_UPDATED    => time(),
                    BaseModel::UPDATED_AT       => Util::now(),
                    BaseModel::STAMP_UPDATED_BY => Auth::id(),
                ]);
            } else {
                throw AuthException::permissionNoExistsInGroup($objAuthGroup, $objAuthPerm);
            }
        } else {
            throw new Exception("$intValue must be 0 or 1", 417);
        }

        return ($objAuthGroup);
    }

    /**
     * @param array $arrParams
     * @param User $objUser
     * @param Project $objProject
     * @return User
     */
    public function updateProjectGroupPermissions(array $arrPerms, User $objUser, Project $objProject): User {
        $objAuthGroup = $this->groupRepo->findByProject($objProject);

        foreach ($arrPerms as $perm) {
            $objAuthPerm = $this->findByName($perm["permission_name"]);
            $intValue = $perm["permission_value"];
            $objUser = $this->updateUserPermission($intValue, $objAuthPerm, $objAuthGroup, $objUser);
        }

        return ($objUser);
    }

    /**
     * @param string $strName
     *
     * @return AuthPermission
     */
    public function findByName(string $strName): AuthPermission {
        return ($this->permRepo->findByName($strName));
    }

    /**
     * @param int $intValue
     * @param AuthPermission $objAuthPerm
     * @param AuthGroup $objAuthGroup
     * @param User $objUser
     *
     * @return User
     */
    public function updateUserPermission(int $intValue, AuthPermission $objAuthPerm, AuthGroup $objAuthGroup, User $objUser): User {
        if ($intValue == 0 || $intValue == 1) {
            $intFlag = $this->checkIfExistsPermission($objAuthPerm, $objAuthGroup, $objUser, $intValue);

            if ($intFlag == Constant::EXIST) {
                $objUser->permissionsInGroup()->newPivotStatement()
                        ->where("core_auth_permissions_groups_users.group_id", $objAuthGroup->group_id)
                        ->where("core_auth_permissions_groups_users.permission_id", $objAuthPerm->permission_id)
                        ->update([
                            "permission_value"          => $intValue,
                            BaseModel::STAMP_UPDATED    => time(),
                            BaseModel::STAMP_UPDATED_BY => Auth::id(),
                            BaseModel::DELETED_AT       => null,
                            BaseModel::STAMP_DELETED    => null,
                            BaseModel::STAMP_DELETED_BY => null,
                        ]);
            } else if ($intFlag == Constant::NOT_EXIST) {
                $objUser->permissionsInGroup()->attach($objAuthPerm->permission_id, [
                    "row_uuid"                  => Util::uuid(),
                    "user_uuid"                 => $objUser->user_uuid,
                    "permission_uuid"           => $objAuthPerm->permission_uuid,
                    "group_id"                  => $objAuthGroup->group_id,
                    "group_uuid"                => $objAuthGroup->group_uuid,
                    "permission_value"          => $intValue,
                    BaseModel::STAMP_CREATED    => time(),
                    BaseModel::STAMP_CREATED_BY => Auth::id(),
                    BaseModel::STAMP_UPDATED    => time(),
                    BaseModel::STAMP_UPDATED_BY => Auth::id(),
                ]);

            }
        } else {
            throw new Exception("$intValue must be between 0 and 1", 417);
        }

        return ($objUser);
    }

}

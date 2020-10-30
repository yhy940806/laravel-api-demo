<?php

namespace App\Repositories\Core\Auth;

use Util;
use Auth;
use Constant;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use App\Models\{BaseModel, Core\Auth\AuthGroup, Core\Auth\AuthPermission, Core\App, Soundblock\Project, Soundblock\Service, User};

class AuthGroupRepository extends BaseRepository {
    /**
     * @param AuthGroup $objAuthGroup
     * @return void
     */
    public function __construct(AuthGroup $objAuthGroup) {
        $this->model = $objAuthGroup;
    }

    /**
     * @param string $user
     * @param int $perPage
     * @param bool $paginate
     * @return mixed
     */
    public function findByUser(string $user, int $perPage = 10, bool $paginate = true) {
        $query = $this->model->whereHas('users', function (Builder $query) use ($user) {
            $query->where('users.user_uuid', $user);
        });

        if ($paginate) {
            $query = $query->paginate($perPage);
        } else {
            $query = $query->get();
        }

        return $query;
    }

    /**
     * @param User $objUser
     * @param string $containingGroupName
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLikelyByUser(User $user, string $containingGroupName) {
        return ($this->model->whereHas('users', function (Builder $query) use ($user) {
            $query->where('users.user_id', $user->user_id);
        })->where("group_name", "like", $containingGroupName)->get());
    }

    /**
     * @param User $objUser
     * @param string $containingGroupName
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function checkUserGroup(User $user, string $containingGroupName) {
        return (
        $this->model->where("group_name", $containingGroupName)
                    ->whereHas('users', function (Builder $query) use ($user) {
                        $query->where('users.user_id', $user->user_id);
                    })->first()
        );
    }

    /**
     * @param array $where
     * @param string $field = "uuid"
     *
     * @return EloquentCollection
     */
    public function findAllWhere(array $where, string $field = "uuid") {
        if ($field == "uuid" || $field == "id") {
            return ($this->model->whereIn("group_" . $field, $where)->get());
        } else if ($field == "name") {
            $arrWhere = Util::filterName($where);

            return ($this->model->Where(function ($query) use ($arrWhere) {
                foreach ($arrWhere as $where) {
                    if (strpos($where, "%") !== false) {
                        $query->orwhere("group_name", "like", $where);
                    } else {
                        $query->orwhere("group_name", $where);
                    }
                }
            })->get());
        } else {
            throw new \Exception();
        }
    }

    /**
     * @param Project $project
     *
     * @return AuthGroup
     */
    public function findByProject(Project $project) {
        $groupName = "App.Soundblock.Project." . $project->project_uuid;
        $authGroup = $this->findByName($groupName);

        return ($authGroup);
    }

    /**
     * @param string $name
     * @return AuthGroup
     */
    public function findByName(string $name): AuthGroup {
        return ($this->model->where("group_name", $name)->firstOrFail());
    }

    /**
     * @param Service $service
     *
     * @return AuthGroup
     */
    public function findByService(Service $service) {
        $groupName = "App.Soundblock.Service." . $service->service_uuid;
        $authGroup = $this->findByName($groupName);

        return ($authGroup);
    }

    public function findAllByPermission(AuthPermission $authPermission, int $perPage = null) {
        $queryBuilder = $this->model->whereHas("permissions", function ($query) use ($authPermission) {
            $query->where("core_auth_permissions_groups.permission_id", $authPermission->permission_id);
        });

        if ($perPage) {
            return ($queryBuilder->paginate($perPage));
        } else {
            return ($queryBuilder->get());
        }
    }

    public function search(array $arrParams) {
        $queryBuilder = $this->model;

        if (isset($arrParams["name"])) {
            $queryBuilder = $queryBuilder->whereRaw("lower(group_name) like (?)", "%" . Util::lowerLabel($arrParams["name"]) . "%");
        }

        if (isset($arrParams["memo"])) {
            $queryBuilder = $queryBuilder->whereRaw("lower(group_memo) like (?)", "%" . Util::lowerLabel($arrParams["memo"]) . "%");
        }

        if (isset($arrParams['select_fields'])) {
            $arrFieldsAliasMap = config("constant.autocomplete.groups.fields_alias");
            $arrFields = explode(",", $arrParams['select_fields']);

            $arrSelect = collect($arrFieldsAliasMap)->only($arrFields)->values()->all();
            $queryBuilder = $queryBuilder->select($arrSelect);
        }

        return $queryBuilder->get();
    }

    public function checkUserInGroupByName(string $groupName, User $user): bool {
        return $this->model->where("group_name", $groupName)->whereHas("users", function (Builder $query) use ($user) {
            $query->where("users.user_id", $user->user_id);
        })->exists();
    }

    /**
     * @param AuthGroup $group
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPermissions(AuthGroup $group) {
        return ($group->permissions()->wherePivot("permission_value", 1)
                      ->select("core_auth_permissions.*", "core_auth_permissions_groups.permission_value")->get());
    }

    /**
     * @param User $objUser
     * @param AuthGroup $objGroup
     * @param App $objApp
     *
     * @return AuthGroup
     */
    public function addUserToGroup(User $objUser, AuthGroup $objGroup, App $objApp): AuthGroup {
        switch ($this->checkIfUserExists($objUser, $objGroup)) {
            case Constant::EXIST:
            {
                break;
            }
            case Constant::NOT_EXIST:
            {
                $objGroup = $this->attachUserToGroup($objUser, $objGroup, $objApp);
                break;
            }
            case Constant::SOFT_DELETED:
            {
                $objGroup = $this->restoreUserInGroup($objUser, $objGroup);
                break;
            }
        }

        return ($objGroup);
    }

    /**
     * @param User $objUser
     * @param AuthGroup $objGroup
     *
     * @return int
     */
    public function checkIfUserExists(User $objUser, AuthGroup $objGroup): int {
        if ($objGroup->users()->wherePivot("user_id", $objUser->user_id)->exists()) {
            return (Constant::EXIST);
        } else if ($objGroup->usersWithTrashed()->wherePivot("user_id", $objUser->user_id)->exists()) {
            return (Constant::SOFT_DELETED);
        } else {
            return (Constant::NOT_EXIST);
        }
    }

    /**
     * @param User $objUser
     * @param AuthGroup $objGroup
     * @param App $objApp
     *
     * @return AuthGroup
     */
    public function attachUserToGroup(User $objUser, AuthGroup $objGroup, App $objApp): AuthGroup {
        $objGroup->users()->attach($objUser->user_id, [
            "row_uuid"                  => Util::uuid(),
            "group_uuid"                => $objGroup->group_uuid,
            "app_id"                    => $objApp->app_id,
            "app_uuid"                  => $objApp->app_uuid,
            "user_uuid"                 => $objUser->user_uuid,
            BaseModel::CREATED_AT       => Util::now(),
            BaseModel::STAMP_CREATED    => time(),
            BaseModel::STAMP_CREATED_BY => Auth::id(),
            BaseModel::UPDATED_AT       => Util::now(),
            BaseModel::STAMP_UPDATED    => time(),
            BaseModel::STAMP_UPDATED_BY => Auth::id(),
        ]);

        return ($objGroup);
    }

    public function restoreUserInGroup(User $objUser, AuthGroup $objGroup): AuthGroup {
        $objGroup->users()->updateExistingPivot($objUser->user_id, [
            BaseModel::UPDATED_AT       => Util::now(),
            BaseModel::STAMP_DELETED    => time(),
            BaseModel::STAMP_UPDATED_BY => Auth::id(),
            BaseModel::DELETED_AT       => null,
            BaseModel::STAMP_DELETED    => null,
            BaseModel::STAMP_DELETED_BY => null,
        ]);

        return ($objGroup);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $arrUsers
     * @param AuthGroup $objGroup
     *
     * @return AuthGroup
     */
    public function detachUsersFromGroup($arrUsers, AuthGroup $objGroup): AuthGroup {
        $objGroup->users()->newPivotStatement()
                 ->where("group_id", $objGroup->group_id)
                 ->whereIn("user_id", $arrUsers->pluck("user_id"))
                 ->update([
                     BaseModel::DELETED_AT       => Util::now(),
                     BaseModel::STAMP_DELETED    => time(),
                     BaseModel::STAMP_DELETED_BY => Auth::id(),
                 ]);

        return ($objGroup);
    }
}

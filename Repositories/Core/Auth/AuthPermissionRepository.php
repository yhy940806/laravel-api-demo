<?php

namespace App\Repositories\Core\Auth;

use Util;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use App\Models\{Core\Auth\AuthGroup, Core\Auth\AuthPermission, User};
use Illuminate\Support\Collection as SupportCollection;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class AuthPermissionRepository extends BaseRepository {
    public function __construct(AuthPermission $objAuthPerm) {
        $this->model = $objAuthPerm;
    }

    public function findAllWhere(array $where, string $field = "uuid") {
        if ($field == "uuid" || $field == "id") {
            return ($this->model->whereIn("permission_" . $field, $where)->get());
        } else if ($field == "name") {

            $arrWhere = Util::filterName($where);

            return ($this->model->Where(function ($query) use ($arrWhere) {
                foreach ($arrWhere as $where) {
                    if (strpos($where, "%") !== false) {
                        $query->orwhere("permission_name", "like", $where);
                    } else {
                        $query->orwhere("permission_name", $where);
                    }
                }
            })->get());

        } else {
            throw new InvalidParameterException();
        }
    }

    /**
     * @param string $strName
     * @param bool $fail
     * @return Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function findByName(string $strName, bool $fail = true) {
        /** @var Builder $query */
        $query = $this->model->whereRaw("lower(permission_name) = (?)", Util::lowerLabel($strName));

        if ($fail) {
            return $query->firstOrFail();
        }

        return $query->first();
    }

    public function findAllByName($name) {
        if (is_array($name)) {
            $arrNames = Util::filterName($name);
            return ($this->model->Where(function ($query) use ($arrNames) {

                foreach ($arrNames as $name) {
                    if (strpos($name, "%") !== false) {
                        $query->orwhere("permission_name", "like", $name);
                    } else {
                        $query->orwhere("permission_name", $name);
                    }
                }

            })->get());
        } else if (is_string($name)) {
            $name = Util::filterName($name);
            return ($this->model->Where(function ($query) use ($name) {
                if (strpos($name, "%") !== false) {
                    $query->where("permission_name", "like", $name);
                } else {
                    $query->where("permission_name", $name);
                }
            })->get());
        } else {
            throw new InvalidParameterException();
        }
    }

    /**
     * @param AuthGroup $group
     * @return SupportCollection
     */
    public function findAllByGroup(AuthGroup $group): SupportCollection {
        return ($group->permissions()->wherePivot("permission_value", 1)
                      ->select("core_auth_permissions.*", "core_auth_permissions_groups.permission_value")->get());
    }

    /**
     * @param User $user
     * @param AuthGroup $objGroup
     * @return SupportCollection
     */
    public function findAllByUserAndGroup(User $user, AuthGroup $objGroup): SupportCollection {
        return ($user->permissionsInGroup()
                     ->select("core_auth_permissions.*", "core_auth_permissions_groups_users.permission_value")
                     ->wherePivot("group_id", $objGroup->group_id)->get()->makeVisible("permission_id"));
    }
}

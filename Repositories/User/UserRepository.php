<?php

namespace App\Repositories\User;

use Util;
use Exception;
use App\Repositories\BaseRepository;
use App\Models\{User, Core\Auth\AuthPermission};
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository {
    public function __construct(User $objUser) {
        $this->model = $objUser;
    }

    public function findAllWhere(array $where, $field = "uuid") {
        if ($field == "uuid" || $field == "id") {
            return ($this->model->whereIn("user_" . $field, $where)->get());
        } else {
            throw new Exception("$field is invalid parameter");
        }
    }

    public function createModel(array $arrParams) {
        $model = new User;
        if (!isset($arrParams["user_uuid"]))
            $arrParams["user_uuid"] = Util::uuid();
        $model->fill($arrParams);

        $model->save();
        return ($model);
    }

    /**
     * @param User $user
     * @return User
     */
    public function updateUserAvatar(User $user): User {
        $user->update([
            "flag_avatar" => true,
        ]);

        return ($user);
    }

    public function findAllByPermission(AuthPermission $objAuthPerm, int $perPage = null) {
        /** @var \Illuminate\Database\Eloquent\Builder */
        $queryBuilder = $this->model->with(["groupsWithPermissions" => function ($query) use ($objAuthPerm) {
            $query->where("permission_id", $objAuthPerm->permission_id);
        }])->whereHas("permissionsInGroup", function ($query) use ($objAuthPerm) {
            $query->where("core_auth_permissions_groups_users.permission_id", $objAuthPerm->permission_id);
        });
        if ($perPage) {
            return ($queryBuilder->paginate($perPage));
        } else {
            return ($queryBuilder->get());
        }
    }

    /**
     * @param array $emails
     *
     * @return Collection
     */
    public function findAllByEmails(array $emails): Collection {
        return ($this->model->whereHas("emails", function ($query) use ($emails) {
            $query->whereIn("user_auth_email", $emails);
        })->get());
    }

    public function search(array $arrParams) {
        $userQuery = $this->model;
        if (isset($arrParams["user"])) {
            $userQuery = $userQuery->whereHas("emails", function ($q) use ($arrParams) {
                $q->whereRaw("lower(user_auth_email) like (?)", "%" . Util::lowerLabel($arrParams["user"]) . "%");
            })->orWhereHas("aliases", function ($q) use ($arrParams) {
                $q->whereRaw("lower(user_alias) like (?)", "%" . Util::lowerLabel($arrParams["user"]) . "%");
            });
        }

        return $userQuery->get();
    }

    public function getLast(): ?User {
        return ($this->model->latest("user_id")->first());
    }

    public function findAllAfter(?int $lastId = 0): Collection {
        return ($this->model->where("user_id", ">", $lastId)->get());
    }

    /**
     * @param User $user
     * @param array $load
     *
     * @return User
     */
    public function getPrimary(User $user) {
        return ($user->setAppends(["avatar", "name", "primary_email", "primary_phone"]));
    }

    /**
     * @param string $strUUID
     * @return mixed
     */
    public function getUserByUUID(string $strUUID) {
        $objUser = $this->model->where("user_uuid", $strUUID)->first();

        return ($objUser);
    }
}

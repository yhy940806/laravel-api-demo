<?php

namespace App\Repositories\Office;

use Util;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\{Core\Auth\AuthGroup, SupportTicket, User};

class SupportTicketRepository extends BaseRepository {
    
    /**
     * @param SupportTicket $objTicket
     * @return void
     */
    public function __construct(SupportTicket $objTicket) {
        $this->model = $objTicket;
    }

    /**
     * @param array $arrParams
     * @param int $perPage
     * @param array $groups
     * @param User $objUser
     * @return \Illuminate\Database\Eloquent\Collection|LengthAwarePaginator
     */
    public function findAll(array $arrParams, int $perPage = null, ?array $groups = null, ?User $objUser = null) {
        $query = $this->model->join("support", "support_tickets.support_id", "=", "support.support_id")
            ->join("core_apps", "support.app_id", "=", "core_apps.app_id")
            ->with(["support", "supportUser", "supportGroup", "support.app"])
            ->where(function ($where) use ($groups, $objUser) {
                $userCheckMethod = "whereHas";

                if(isset($groups)) {
                    $userCheckMethod = "orWhereHas";

                    $where = $where->whereHas("supportGroup", function (Builder $query) use ($groups) {
                        $query->whereIn('core_auth_groups.group_id', $groups);
                    });
                }

                if(isset($objUser)) {
                    $where = $where->{$userCheckMethod}("supportUser", function (Builder $query) use ($objUser) {
                        $query->where('users.user_id', $objUser->user_id);
                    });
                }
            })->select("support_tickets.*");

        return ($this->applyFilter($query, $arrParams, $perPage));
    }

    /**
     * @param User $objUser
     * @param array $arrParams
     * @param int $perPage
     * @return \Illuminate\Database\Eloquent\Collection|LengthAwarePaginator
     */
    public function findAllByUser(User $objUser, array $arrParams, ?int $perPage = null) {
        $query = $this->model->join("support", "support_tickets.support_id", "=", "support.support_id")
            ->join("core_apps", "support.app_id", "=", "core_apps.app_id")
            ->with(["support", "support.app"])
            ->where(function ($where) use ($objUser) {
                $where->where("support_tickets.user_id", $objUser->user_id)
                    ->orWhereHas("supportUser", function (Builder $query) use ($objUser) {
                        $query->where('users.user_id', $objUser->user_id);
                    });
            })->select("support_tickets.*");

        return ($this->applyFilter($query, $arrParams, $perPage));
    }

    /**
     * @param array $arrParams
     * @param AuthGroup $objAuthGroup
     * @param int $perPage
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\Paginator
     */
    public function findAllByGroup(array $arrParams, AuthGroup $objAuthGroup, int $perPage) {
        $query = $this->model->join("support", "support_tickets.support_id", "=", "support.support_id")
            ->join("core_apps", "support.app_id", "=", "core_apps.app_id")
            ->with(["support", "supportUser", "supportGroup"])
            ->whereHas("supportGroup", function (Builder $query) use ($objAuthGroup) {
                $query->where('core_auth_groups.group_id', $objAuthGroup->group_id);
            })->select("support_tickets.*");

        return $this->applyFilter($query, $arrParams, $perPage);
    }

    /**
     * @param Builder $query
     * @param array $arrParams
     * @param int $perPage
     * @return \Illuminate\Database\Eloquent\Collection|LengthAwarePaginator
     */
    protected function applyFilter(Builder $query, array $arrParams, ?int $perPage = null) {
        if(isset($arrParams["sort_app"])) {
            $query = $query->orderBy("core_apps.app_name", Util::lowerLabel($arrParams["sort_app"]));
        }

        if(isset($arrParams["sort_support_category"])) {
            $query = $query->orderBy("support.support_category", Util::lowerLabel($arrParams["sort_support_category"]));
        }

        if(isset($arrParams["sort_flag_status"])) {
            $query = $query->orderBy("flag_status", Util::lowerLabel($arrParams["sort_flag_status"]));
        }

        if(isset($arrParams["flag_status"])) {
            $query = $query->whereRaw("lower(support_tickets.flag_status) = (?)", Util::lowerLabel($arrParams["flag_status"]));
        }

        if(isset($arrParams["support_category"])) {
            $query = $query->whereRaw("lower(support.support_category) = (?)", Util::lowerLabel($arrParams["support_category"]));
        }

        if(isset($arrParams["app"])) {
            $query = $query->where("core_apps.app_uuid", $arrParams["app"]);
        }

        if(isset($perPage)) {
            $arrTickets = $query->paginate($perPage);
        } else {
            $arrTickets = $query->get();
        }

        return ($arrTickets);
    }
}

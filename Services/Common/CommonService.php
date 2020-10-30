<?php

namespace App\Services\Common;

use App\Events\Common\CreateService;
use App\Exceptions\CommonServiceException;
use App\Models\{User, Soundblock\Service};
use App\Repositories\Core\Auth\{AuthGroupRepository, AuthPermissionRepository};
use App\Repositories\User\UserRepository;
use App\Repositories\Common\ServiceRepository;
use Auth;
use Exception;
use Util;

class CommonService {
    /** @var ServiceRepository */
    protected ServiceRepository $serviceRepo;
    /** @var AuthPermissionRepository */
    protected AuthPermissionRepository $permRepo;
    /** @var AuthGroupRepository */
    protected AuthGroupRepository $groupRepo;
    /** @var UserRepository */
    protected UserRepository $userRepo;

    /**
     * @param AuthPermissionRepository $permRepo
     * @param AuthGroupRepository $groupRepo
     * @param UserRepository $userRepo
     * @param ServiceRepository $serviceRepo
     */
    public function __construct(AuthPermissionRepository $permRepo, AuthGroupRepository $groupRepo, UserRepository $userRepo, ServiceRepository $serviceRepo) {
        $this->permRepo = $permRepo;
        $this->groupRepo = $groupRepo;
        $this->userRepo = $userRepo;
        $this->serviceRepo = $serviceRepo;
    }

    /**
     * @param string $serviceName
     * @param User $objUser
     * @return Service
     * @throws Exception
     */
    public function create(string $serviceName, ?User $objUser = null): Service {
        if (is_null($objUser))
            $objUser = Auth::user();
        $arrService = [];
        $objService = $objUser->service;

        try {
            if (!$objService) {
                $arrService["user_id"] = $objUser->user_id;
                $arrService["user_uuid"] = $objUser->user_uuid;
                $arrService["service_name"] = $serviceName;
            } else {
                throw CommonServiceException::cantCreateService(Auth::user()->user_uuid);
            }
        } catch (Exception $e) {
            throw $e;
        }

        $objService = $this->serviceRepo->create($arrService);

        $arrAuthGroup = [
            "group_type" => "service",
            "object"     => $objService,
            "user"       => $objUser,
        ];
        event(new CreateService($arrAuthGroup));

        return ($objService);
    }

    public function findAll($perPage = 10) {
        return (Service::paginate($perPage)
                       ->withPath(route("get-services")));
    }

    public function findAllLikeName(string $term, string $column = "service_name") {
        return ($this->serviceRepo->findAllLikeName($term, $column));
    }

    public function findByUser(User $objUser = null, $blnBelongsTo = false) {
        $arrServices = collect();

        if (!$objUser) {
            $objUser = Auth::user();
        }

        //Permission for creating a project.
        $reqPerm = "App.Soundblock.Service.Project.Create";
        $reqGroup = "App.Soundblock.Service.%";
        $objPerm = $this->permRepo->findByName($reqPerm);

        // $arrGroups = $this->groupRepo->findAllWhere(array($reqGroup), "name");

        // $arrGroupIds = $arrGroups->pluck("group_id");

        $objUser->load(["groups" => function ($q) {
            $q->where("core_auth_groups.group_name", "like", "App.Soundblock.Service.%");
        }]);

        $arrUserGroups = $objUser->groups->unique("group_id");

        // $objUser->load(["groups_permissions" => function ($q) use($reqGroup, $objPerm){
        //     $q->where("auth_groups.group_name", "like", $reqGroup);
        //     $q->wherePivot("permission_id", $objPerm->permission_id);
        //     $q->wherePivot("permission_value" , 0);
        // }]);

        // $arrNoCreateGroups = $objUser->groups_permissions->unique("group_id");

        // $arrGroups = $arrUserGroups->reject(function ($value, $key) use($arrNoCreateGroups) {

        //     foreach($arrNoCreateGroups as $objGroup)
        //     {
        //         if ($objGroup->group_id == $value->group_id)
        //             return(true);
        //     }
        // });
        $arrGroups = $arrUserGroups;

        foreach ($arrGroups as $objGroup) {
            //service_uuid -> $objService
            $service = Util::uuid($objGroup->group_name);
            if ($service)
                $arrServices->push($this->find($service));
        }

        return ($arrServices);
    }

    public function find($id, bool $bnFailure = true) {
        return ($this->serviceRepo->find($id, $bnFailure));
    }

    /**
     * @param User|null $objUser
     * @param $strServiceUuid
     * @return Service
     */
    public function findUsersService(User $objUser = null, $strServiceUuid) {
        if (!$objUser) {
            $objUser = Auth::user();
        }

        $reqGroupName = "App.Soundblock.Service." . $strServiceUuid;
        $objProjectGroup = $this->groupRepo->checkUserGroup($objUser, $reqGroupName);

        if (is_null($objProjectGroup)) {
            return (false);
        }

        $objService = $this->serviceRepo->find($strServiceUuid);

        return ($objService);
    }

    public function findByHolder(User $user) {
        return ($user->service);
    }

    public function update(Service $objService, array $arrParams): Service {

        $arrService = [];
        if (isset($arrParams["service_plan_name"])) {
            $arrService["service_name"] = $arrParams["service_plan_name"];
        }
        $objNewService = $this->serviceRepo->update($objService, $arrService);

        return ($objNewService);
    }
}

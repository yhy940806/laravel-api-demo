<?php

namespace App\Http\Controllers\Common;

use Auth;
use App\Http\Controllers\Controller;
use App\Models\Soundblock\{Project, Service};
use App\Http\Transformers\ServiceTransformer;
use App\Services\{AuthService, Soundblock\ProjectService};
use App\Http\Requests\Office\Service\TypeaheadServiceRequest;
use App\Services\Core\Auth\AuthPermissionService;
use App\Services\Common\CommonService;
use App\Http\Requests\Soundblock\Service\{CreateServiceRequest,
    GetServiceByProjectRequest,
    UpdateServiceRequest,
    GetServiceRequest};

/**
 * @group Service
 *
 * APIs for the service.
 */
class ServiceController extends Controller {
    /** @var AuthService */
    protected AuthService $authService;
    /** @var AuthPermissionService */
    protected AuthPermissionService $authPermService;

    /**
     * @param AuthService $authService
     * @param AuthPermissionService $authPermService
     * @return void
     */
    public function __construct(AuthService $authService, AuthPermissionService $authPermService) {
        $this->authService = $authService;
        $this->authPermService = $authPermService;
    }

    /**
     * @responseFile responses/office/soundblock/service/serviceplans.get.json
     * @param CommonService $commonService
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function index(CommonService $commonService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $arrServices = $commonService->findAll();
                return ($this->response->paginator($arrServices, new ServiceTransformer(["user", "plans"])));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/office/service/typeahead.get.json
     * @param TypeaheadServiceRequest $objRequest
     * @param CommonService $commonService
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function search(TypeaheadServiceRequest $objRequest, CommonService $commonService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $arrServices = $commonService->findAllLikeName($objRequest->service_name);
                return ($this->response->collection($arrServices, new ServiceTransformer));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/soundblock/index.soundblock.get.json
     * @param CommonService $commonService
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function indexForSoundblock(CommonService $commonService) {
        try {
            $arrServices = $commonService->findByUser(Auth::user());
            return ($this->response->collection($arrServices, new ServiceTransformer(["user"])));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @urlParam service required string The Service UUID
     * @responseFile responses/soundblock/service/get-service.get.json
     * @param string $service The Service UUID
     * @param CommonService $commonService
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function show(string $service, CommonService $commonService) {
        try {
            $objService = $commonService->find($service);
            return ($this->response->item($objService, new ServiceTransformer(["plans"])));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getByProject(GetServiceByProjectRequest $objRequest, ProjectService $projectService) {
        try {
            /** @var Project */
            $objProject = $projectService->find($objRequest->project, true);
            /** @var Service */
            $objService = $objProject->service;
            return ($this->apiReply($objService->load(["plans", "transactions.chargeType"])));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function userService() {
        try {
            /** @var User */
            $objUser = Auth::user();

            if ($objUser->service) {
                return ($this->response->item($objUser->service, new ServiceTransformer(["plans"], true, true)));
            } else {
                abort(400, "The user has not his own service.");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @queryParam service string required Service UUID
     * @responseFile responses/office/soundblock/service/get-service.get.json
     *
     * @param GetServiceRequest $objRequest
     * @param CommonService $commonService
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function showForOffice(GetServiceRequest $objRequest, CommonService $commonService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objService = $commonService->find($objRequest->service);
                return ($this->response->item($objService, new ServiceTransformer(["user", "plans"])));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/soundblock/service/create.post.json
     * @bodyParam user uuid optional User UUID
     * @bodyParam service_name string required
     *
     * @param CreateServiceRequest $objRequest
     * @param CommonService $commonService
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function store(CreateServiceRequest $objRequest, CommonService $commonService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objService = $commonService->create($objRequest->service_name);
                return ($this->response->item($objService, new ServiceTransformer));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/soundblock/service/update.patch.json
     * @bodyParam service uuid required Service UUID
     * @bodyParam service_plan_name string required
     *
     * @param UpdateServiceRequest $objRequest
     * @param CommonService $commonService
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */

    public function update(UpdateServiceRequest $objRequest, CommonService $commonService) {
        try {
            $objService = $commonService->find($objRequest->service);
            $objService = $commonService->update($objService, $objRequest->all());
            return ($this->response->item($objService, new ServiceTransformer));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Test endpoint.
     */
    public function test() {
        return ($this->commonService->findByUser());
    }
}

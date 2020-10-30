<?php

namespace App\Http\Controllers\Account;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Traits\Account\FindUserProject;
use App\Http\Requests\Account\Soundblock\GetProjectsRequest;
use App\Http\Transformers\{Soundblock\ServicePlanTransformer,
    ServiceTransformer,
    User\UserTransformer,
    Soundblock\TeamTransformer,
    Account\ProjectTransformer,
    Soundblock\DeploymentTransformer,
    Soundblock\TransactionTransformer
};
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Services\{AuthService, Common\CommonService, Soundblock\ProjectService};

class SoundblockController extends Controller {
    use FindUserProject;

    /**
     * @var AuthService
     */
    private AuthService $authService;
    /**
     * @var ProjectService
     */
    private ProjectService $projectService;

    /**
     * SoundblockController constructor.
     * @param AuthService $authService
     * @param ProjectService $projectService
     */
    public function __construct(AuthService $authService, ProjectService $projectService) {
        $this->authService = $authService;
        $this->projectService = $projectService;
    }

    /**
     * @group Accounting
     * @queryParam per_page  Items per page
     * @param GetProjectsRequest $objRequest
     * @return \Dingo\Api\Http\Response
     */
    public function getProjects(GetProjectsRequest $objRequest) {
        if (!$this->authService->checkApp("account")) {
            throw new AccessDeniedHttpException("Invalid App");
        }

        /** @var User $objUser */
        $objUser = Auth::user();

        if ($objRequest->has('per_page')) {
            $perPage = $objRequest->input('per_page');
        } else {
            $perPage = 10;
        }

        $objProjects = $this->projectService->findAllByUser($perPage, $objUser);

        return ($this->response->paginator($objProjects, new ProjectTransformer));
    }

    /**
     * @group Accounting
     * @urlParam project_uuid required Project UUID
     * @param $strProjectUuid
     * @return \Dingo\Api\Http\Response
     */
    public function getProject($strProjectUuid) {
        if (!$this->authService->checkApp("account")) {
            throw new AccessDeniedHttpException("Invalid App");
        }

        $objProject = $this->getUserProject($strProjectUuid);

        return ($this->response->item($objProject, new ProjectTransformer));
    }

    /**
     * @group Accounting
     * @urlParam project_uuid required Project UUID
     * @param $strProjectUuid
     * @return \Dingo\Api\Http\Response
     */
    public function getProjectDeployments($strProjectUuid) {
        if (!$this->authService->checkApp("account")) {
            throw new AccessDeniedHttpException("Invalid App");
        }

        $objProject = $this->getUserProject($strProjectUuid);

        return ($this->response->item($objProject->deployments, new DeploymentTransformer));
    }

    /**
     * @group Accounting
     * @urlParam project_uuid required Project UUID
     * @param $strProjectUuid
     * @return \Dingo\Api\Http\Response
     */
    public function getProjectService($strProjectUuid) {
        if (!$this->authService->checkApp("account")) {
            throw new AccessDeniedHttpException("Invalid App");
        }

        $objProject = $this->getUserProject($strProjectUuid);

        return ($this->response->item($objProject->service, new ServiceTransformer(["plans"])));
    }

    /**
     * @group Accounting
     * @urlParam project_uuid required Project UUID
     * @param $strProjectUuid
     * @return \Dingo\Api\Http\Response
     */
    public function getProjectMembers($strProjectUuid) {
        if (!$this->authService->checkApp("account")) {
            throw new AccessDeniedHttpException("Invalid App");
        }

        $objProject = $this->getUserProject($strProjectUuid);

        return ($this->response->item($objProject->team, new TeamTransformer(["users"])));
    }

    /**
     * @group Accounting
     * @param CommonService $commonService
     * @return \Dingo\Api\Http\Response
     */
    public function getServices(CommonService $commonService) {
        if (!$this->authService->checkApp("account")) {
            throw new AccessDeniedHttpException("Invalid App");
        }

        $arrServices = $commonService->findByUser(Auth::user());

        return ($this->response->collection($arrServices, new ServiceTransformer));
    }

    /**
     * @group Accounting
     * @urlParam service_uuid required Service UUID
     * @param $strServiceUuid
     * @param CommonService $commonService
     * @return \Dingo\Api\Http\Response
     */
    public function getService($strServiceUuid, CommonService $commonService) {
        $objService = $this->getUsersService($strServiceUuid, $commonService);

        return ($this->response->item($objService, new ServiceTransformer));
    }

    /**
     * @param $strServiceUuid
     * @param CommonService $commonService
     * @return \App\Models\Soundblock\Service
     */
    public function getUsersService($strServiceUuid, CommonService $commonService) {
        if (!$this->authService->checkApp("account")) {
            throw new AccessDeniedHttpException("Invalid App");
        }

        $objService = $commonService->findUsersService(Auth::user(), $strServiceUuid);

        if (!$objService) {
            abort(404);
        }

        return ($objService);
    }

    /**
     * @group Accounting
     * @urlParam service_uuid required Service UUID
     * @param $strServiceUuid
     * @param CommonService $commonService
     * @return \Dingo\Api\Http\Response
     */
    public function getServiceTransaction($strServiceUuid, CommonService $commonService) {
        $objService = $this->getUsersService($strServiceUuid, $commonService);

        return ($this->response->item($objService->transactions, new TransactionTransformer));
    }

    /**
     * @group Accounting
     * @urlParam service_uuid required Service UUID
     * @param $strServiceUuid
     * @param CommonService $commonService
     * @return \Dingo\Api\Http\Response
     */
    public function getServicePlan($strServiceUuid, CommonService $commonService) {
        $objService = $this->getUsersService($strServiceUuid, $commonService);

        $objPlan = $objService->plans()->active()->first();

        if (is_null($objPlan)) {
            abort(404, "Service doesn't have active plan");
        }

        return ($this->response->item($objPlan, new ServicePlanTransformer()));
    }

    /**
     * @group Accounting
     * @urlParam service_uuid required Service UUID
     * @param $strServiceUuid
     * @param CommonService $commonService
     * @return \Dingo\Api\Http\Response
     */
    public function getServiceUser($strServiceUuid, CommonService $commonService) {
        $objService = $this->getUsersService($strServiceUuid, $commonService);

        return ($this->response->item($objService->user, new UserTransformer));
    }
}

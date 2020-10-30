<?php

namespace App\Http\Controllers\Common;

use Exception;
use App\Services\AuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Common\Job\{UpdateJobRequest};
use App\Services\Core\Auth\{AuthGroupService, AuthPermissionService};
use App\Services\Common\QueueJobService;

class QueueJobController extends Controller {
    /** @var AuthService */
    protected AuthService $authService;
    /** @var AuthGroupService */
    protected AuthGroupService $authGroupService;
    /** @var AuthPermissionService */
    protected AuthPermissionService $authPermService;

    /**
     * @param AuthService $authService
     * @param AuthGroupService $authGroupService
     * @param AuthPermissionService $authPermService
     * @return void
     */
    public function __construct(AuthService $authService, AuthGroupService $authGroupService, AuthPermissionService $authPermService) {
        $this->authService = $authService;
        $this->authGroupService = $authGroupService;
        $this->authPermService = $authPermService;
    }

    public function index(QueueJobService $qjService) {

    }

    /**
     * @param string $job
     * @param QueueJobService $qjService
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function show(string $job, QueueJobService $qjService) {
        try {
            $arrStatus = $qjService->getStatus($job);
            return ($this->apiReply($arrStatus));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param QueueJobService $qjService
     * @return mixed \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function status(QueueJobService $qjService) {
        try {
            $arrStatus = $qjService->getJobsStatus();
            return ($this->apiReply($arrStatus));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param UpdateJobRequest $objRequest
     * @param QueueJobService $qjService
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function update(UpdateJobRequest $objRequest, QueueJobService $qjService) {
        try {
            $objQueueJob = $qjService->find($objRequest->job);
            $objQueueJob = $qjService->update($objQueueJob, $objRequest->all());

            return ($this->apiReply($objQueueJob));
        } catch (Exception $e) {
            throw $e;
        }
    }
}

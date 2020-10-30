<?php

namespace App\Listeners\Office;

use Log;
use App\Services\Soundblock\DeploymentService;

class UpdateDeploymentListener {
    protected DeploymentService $deploymentService;

    /**
     * Create the event listener.
     *
     * @param DeploymentService $deploymentService
     */
    public function __construct(DeploymentService $deploymentService) {
        $this->deploymentService = $deploymentService;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event) {
        $objDeployment = $event->objDeployment;

        if ($objDeployment->status) {
            $objStatus = $objDeployment->status;
        } else {
            Log::info("Create - status", [$objDeployment->deployment_uuid]);
            $objStatus = $this->deploymentService->createDeploymentStatus($objDeployment);
        }

        $this->deploymentService->updateDeploymentStatus($objStatus, $objDeployment);
    }
}

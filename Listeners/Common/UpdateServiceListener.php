<?php

namespace App\Listeners\Common;

use App\Services\Core\Auth\AuthGroupService;

class UpdateServiceListener {

    protected AuthGroupService $authGroupService;

    /**
     * Create the event listener.
     *
     * @param AuthGroupService $authGroupService
     */
    public function __construct(AuthGroupService $authGroupService) {
        $this->authGroupService = $authGroupService;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event) {
        //
        $objService = $event->objService;
        $objNewService = $event->objNewService;
        $objAuthGroup = $this->authGroupService->findByService($objService);
    }
}

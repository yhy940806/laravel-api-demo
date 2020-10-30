<?php

namespace App\Listeners\Common;

use App\Events\Common\CreateService;
use App\Services\Core\Auth\AuthGroupService;
use App\Services\Core\Auth\AuthPermissionService;
use Auth;
use Constant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Log;

class CreateServiceListener {

    protected AuthGroupService $authGroupService;

    protected AuthPermissionService $authPermService;

    protected $arrAuthGroup;

    /**
     * Create the event listener.
     *
     * @param AuthGroupService $authGroupService
     * @param AuthPermissionService $authPermService
     */
    public function __construct(AuthGroupService $authGroupService, AuthPermissionService $authPermService) {
        $this->authGroupService = $authGroupService;
        $this->authPermService = $authPermService;
    }

    /**
     * Handle the event.
     *
     * @param CreateService $event
     * @return void
     */
    public function handle(CreateService $event) {
        $this->arrAuthGroup = $event->arrAuthGroup;
        $objAuthGroup = $this->authGroupService->createGroup($this->arrAuthGroup, true);

        $this->authPermService->attachGroupPermissions(Constant::service_level_permissions(), $objAuthGroup);
        $this->authPermService->attachUserPermissions(Constant::user_level_permissions(), Auth::user(), $objAuthGroup);
    }
}

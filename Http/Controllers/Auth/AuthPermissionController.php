<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Http\Controllers\Controller;
use App\Services\{AuthService, UserService};
use App\Http\Resources\Common\{BaseCollection, UserCollection};
use App\Services\Core\Auth\{AuthGroupService, AuthPermissionService};
use App\Http\Requests\Auth\Access\{
    GetPermissionsRequest,
    AddPermissionsToGroupRequest,
    AddPermissionsToUserRequest,
    DeletePermissionsInGroupRequest
};
use App\Http\Requests\Office\Auth\AuthPermission\{CreateAuthPermissiionRequest,
    DeletePermissionInUserRequest,
    GetAuthPermissionRequest,
    UpdateAuthPermissionInGroupRequest,
    UpdateAuthPermissionInUserRequest,
    UpdatePermissionRequest
};

/**
 * @group Authentication
 */
class AuthPermissionController extends Controller {

    /**
     * @var AuthPermissionService
     */
    protected AuthPermissionService $authPermService;
    /**
     * @var AuthService
     */
    protected AuthService $authService;
    /**
     * @var UserService
     */
    protected UserService $userService;

    /**
     * AuthPermissionController constructor.
     * @param AuthService $authService
     * @param AuthPermissionService $authPermService
     * @param UserService $userService
     */
    public function __construct(AuthService $authService, AuthPermissionService $authPermService, UserService $userService) {
        $this->authService = $authService;
        $this->authPermService = $authPermService;
        $this->userService = $userService;
    }

    /**
     * @responseFile responses/office/auth/get-permissions.get.json
     * @return \Dingo\Api\Http\Response
     */

    public function index() {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $arrAuthPerms = $this->authPermService->findAll();

                return (new BaseCollection($arrAuthPerms));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            return ($this->handle($e));
        }
    }

    /**
     * @responseFile responses/office/auth/get-users-groups-permission.get.json
     * @param GetAuthPermissionRequest $objRequest
     * @return \Dingo\Api\Http\Response
     */

    public function show(GetAuthPermissionRequest $objRequest) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objAuthPerm = $this->authPermService->find($objRequest->permission);

                return ($this->apiReply($objAuthPerm));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            return ($this->handle($e));
        }
    }

    /**
     * @urlParam permission required Permission UUID
     * @queryParam per_page optional Items per page
     *
     * @param GetAuthPermissionRequest $objRequest
     * @param AuthGroupService $authGroupService
     * @param string $permission
     * @return \Dingo\Api\Http\Response
     */
    public function getGroups(GetAuthPermissionRequest $objRequest, AuthGroupService $authGroupService, string $permission) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                if (isset($objRequest->per_page)) {
                    $perPage = $objRequest->per_page;
                } else {
                    $perPage = 10;
                }

                $objAuthPerm = $this->authPermService->find($permission);
                /** @var \Illuminate\Pagination\Paginator */
                return (new BaseCollection($authGroupService->findAllByPermission($objAuthPerm, $perPage)));
            } else {
                abort(403, "You have not required permission.");
            }

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @urlParam permission required Permission UUID
     *
     * @param UserService $userService
     * @param string $permission
     * @return \Dingo\Api\Http\Response
     */
    public function getUsers(UserService $userService, string $permission) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];
            if ($this->authService->checkAuth($reqOffice)) {
                $objAuthPerm = $this->authPermService->find($permission);

                return (new UserCollection($userService->findAllByPermission($objAuthPerm)));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param CreateAuthPermissiionRequest $objRequest
     * @return \Dingo\Api\Http\Response
     */
    public function store(CreateAuthPermissiionRequest $objRequest) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objAuthPerm = $this->authPermService->create($objRequest->all());

                return ($this->apiReply($objAuthPerm));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/auth/get-permissions-in-group.get.json
     * @param GetPermissionsRequest $objRequest
     * @param AuthGroupService $authGroupService
     * @return \Dingo\Api\Http\Response
     */

    public function getPermissionsInGroup(GetPermissionsRequest $objRequest, AuthGroupService $authGroupService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {

                $objAuthGroup = $authGroupService->find($objRequest->group);

                return ($this->apiReply($objAuthGroup->permissions));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/auth/add-permissions.post.json
     * @responseFile 417 responses/auth/add-permissions_error.post.json
     *
     * @param AddPermissionsToGroupRequest $objRequest
     * @return \Dingo\Api\Http\Response
     */
    public function addPermissionsToGroup(AddPermissionsToGroupRequest $objRequest) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objAuthGroup = $this->authPermService->addGroupPermissions($objRequest->all());

                return ($this->apiReply($objAuthGroup->load("permissions")));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @urlParam permission required Permission UUID
     * @bodyParam group string required Group UUID
     * @bodyParam permission_value bool required Permission Value
     *
     * @param UpdateAuthPermissionInGroupRequest $objRequest
     * @param AuthGroupService $authGroupService
     * @param string $permission
     * @return \Dingo\Api\Http\Response
     */
    public function updatePermissionInGroup(UpdateAuthPermissionInGroupRequest $objRequest, AuthGroupService $authGroupService, string $permission) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objAuthGroup = $authGroupService->find($objRequest->input("group"));
                $objAuthPerm = $this->authPermService->find($permission);
                $objAuthGroup = $this->authPermService->updateGroupPermission($objRequest->input("permission_value"), $objAuthPerm, $objAuthGroup);

                return ($this->apiReply($objAuthGroup->load("permissions")));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @bodyParam group string required Group UUID.
     * @bodyParam user string required User UUID.
     * @urlParam permission required Permission UUID.
     * @bodyParam permission_value bool required Permission Value.
     *
     * @param UpdateAuthPermissionInUserRequest $objRequest
     * @param AuthGroupService $authGroupService
     * @param string $permission
     * @return \Dingo\Api\Http\Response
     */
    public function updatePermissionInUser(UpdateAuthPermissionInUserRequest $objRequest, AuthGroupService $authGroupService, string $permission) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objAuthGroup = $authGroupService->find($objRequest->input("group"));
                $objAuthPerm = $this->authPermService->find($permission);
                $objUser = $this->userService->find($objRequest->input("user"));
                $objUser = $this->authPermService->updateUserPermission($objRequest->input("permission_value"), $objAuthPerm, $objAuthGroup, $objUser);

                return ($this->apiReply($objUser->with("groupsWithPermissions")));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @bodyParam group string required Group UUID.
     * @urlParam permission required Permission UUID.
     *
     * @param DeletePermissionsInGroupRequest $objRequest
     * @param string $permission
     * @return \Dingo\Api\Http\Response
     */
    public function deletePermissionInGroup(DeletePermissionsInGroupRequest $objRequest, string $permission) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objAuthGroup = $this->authPermService->deleteGroupPermission(array_merge($objRequest->all(), ["permission" => $permission]));

                return ($this->apiReply($objAuthGroup->load("permissions")));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/auth/add-permissions-to-user.post.json
     *
     * @param AddPermissionsToUserRequest $objRequest
     * @return \Dingo\Api\Http\Response
     */

    public function addPermissionsToUser(AddPermissionsToUserRequest $objRequest) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $this->authPermService->addUserPermissions($objRequest->all());

                return ($this->apiReply($objUser->load("permissionsInGroup")));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @urlParam permission required Permission UUID.
     * @bodyParam group string required Group UUID.
     * @bodyParam user string required User UUID.
     *
     * @param DeletePermissionInUserRequest $objRequest
     * @param string $permission
     * @return \Dingo\Api\Http\Response
     */

    public function deletePermissionInUser(DeletePermissionInUserRequest $objRequest, string $permission) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objUser = $this->authPermService->deleteUserPermission(array_merge($objRequest->all(), ["permission" => $permission]));

                return ($this->apiReply($objUser->load("groupsWithPermissions")));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @urlParam permission required Permission UUID.
     * @bodyParam name string optional Permission Name.
     * @bodyParam memo string optional Permission Memo.
     * @bodyParam critical bool optional Is Critical Flag.
     *
     * @param UpdatePermissionRequest $objRequest
     * @param $permission
     * @return \Dingo\Api\Http\Response
     */
    public function updatePermission(UpdatePermissionRequest $objRequest, $permission) {
        $reqGroupName = "App.Office.Admin";
        $reqPermissionName = "App.Office.Admin.Default";

        try {
            if ($this->authService->isAuthorized($reqGroupName, $reqPermissionName)) {
                $objAuthPerm = $this->authPermService->find($permission);
                $objAuthPerm = $this->authPermService->update($objAuthPerm, $objRequest->all());

                return ($this->apiReply($objAuthPerm));
            } else {
                abort(403);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}

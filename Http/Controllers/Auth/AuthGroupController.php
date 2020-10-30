<?php

namespace App\Http\Controllers\Auth;

use Auth;
use Exception;
use Illuminate\Http\Request;
use App\Exceptions\AuthException;
use App\Http\Controllers\Controller;
use App\Http\Requests\{Office\Auth\AuthGroup\AutoCompleteRequest,
    Auth\Access\AddUserRequest,
    Auth\Access\DeleteGroupRequest,
    Auth\Access\DeleteUsersInGroupRequest,
    Auth\Access\CreateGroupRequest,
    Office\Auth\AuthGroup\GetAuthGroupRequest};
use App\Services\{UserService, AuthService, Core\Auth\AuthGroupService, Core\Auth\AuthPermissionService};
use App\Http\Transformers\{User\UserTransformer, Auth\AuthGroupTransformer, Auth\OnlyAuthGroupTransformer};

/**
 * @group Authentication
 */
class AuthGroupController extends Controller
{
    /**
     * @var AuthService
     */
    protected AuthService $authService;
    /**
     * @var AuthGroupService
     */
    protected AuthGroupService $authGroupService;
    /**
     * @var AuthPermissionService
     */
    protected AuthPermissionService $authPermService;
    /**
     * @var UserService
     */
    private UserService $userService;

    /**
     * AuthGroupController constructor.
     * @param AuthService $authService
     * @param AuthGroupService $authGroupService
     * @param UserService $userService
     * @param AuthPermissionService $authPermService
     */
    public function __construct(AuthService $authService, AuthGroupService $authGroupService, UserService $userService,
                                AuthPermissionService $authPermService) {
        $this->authService = $authService;
        $this->authGroupService = $authGroupService;
        $this->authPermService = $authPermService;
        $this->userService = $userService;
    }

    /**
     * @reponseFile responses/office/auth/get-groups.get.json
     *
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */

    public function index(Request $request) {
        try{
            $reqOffice = [
                "group" => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app" => "office"
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                if($request->has('user')) {
                    $arrAuthGroups = $this->authGroupService->findByUser($request->input('user'));
                } else {
                    $arrAuthGroups = $this->authGroupService->findAll();
                }

                return $this->response->paginator($arrAuthGroups, new OnlyAuthGroupTransformer);
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @queryParam name Group Name.
     * @queryParam memo Group Memo.
     * @queryParam select_fields The list of fields that will selected. Fields: name - group_name, memo - group_memo, is_critical - flag_critical, group - group_uuid, auth - auth_uuid. E.g select_fields=name,memo
     *
     * @param AutoCompleteRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(AutoCompleteRequest $request) {
        try{
            $arrGroups = $this->authGroupService->search($request->all());

            return response()->json($arrGroups);
        } catch(Exception $e) {
            throw $e;
        }
    }

    public function show(GetAuthGroupRequest $objRequest) {
        try{
            $reqOffice = [
                "group" => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app" => "office"
            ];

            if ($this->authService->checkAuth($reqOffice))
            {
                $objAuthGroup = $this->authGroupService->find($objRequest->group);

                return($this->response->item($objAuthGroup, new AuthGroupTransformer(["permissions"])));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/auth/create-group.post.json
     * @responseFile 417 responses/auth/create-group_error.post.json
     * @param CreateGroupRequest $objRequest
     * @return \Dingo\Api\Http\Response
     */

    public function store(CreateGroupRequest $objRequest) {
        try{
            $reqOffice = [
                "group" => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app" => "office"
            ];

            if ($this->authService->checkAuth($reqOffice))
            {
                $objAuthGroup = $this->authGroupService->createGroup($objRequest->all());

                return($this->response->item($objAuthGroup, new AuthGroupTransformer()));
            } else {
                abort(403, "You have not required permission.");
            }

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/auth/add-members.post.json
     * @responseFile 417 responses/auth/add-members_error.post.json
     * @param AddUserRequest $objRequest
     * @return \Dingo\Api\Http\Response
     */

    public function addUsers(AddUserRequest $objRequest) {

        try{
            $reqOffice = [
                "group" => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app" => "office"
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objAuthGroup = $this->authGroupService->addUsers($objRequest->all());

                return($this->response->item($objAuthGroup, new AuthGroupTransformer(["users"])));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/auth/delete-group.delete.json
     * @responseFile 417 responses/auth/delete-group_error.delete.json
     * @param DeleteGroupRequest $objRequest
     * @return
     */

    public function deleteGroup(DeleteGroupRequest $objRequest)
    {
        try{
            $reqOffice = [
                "group" => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app" => "office"
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                if ($this->authGroupService->remove($objRequest->all())) {
                    return($this->apiReply(null, "Successfully group deleted."));
                } else {
                    return($this->apiReject("Deleted failure"));
                }
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @responseFile responses/auth/delete-users-in-group.delete.json
     * @responseFile 417 responses/auth/delete-users-in-group_error.delete.json
     * @param DeleteUsersInGroupRequest $objRequest
     * @return \Dingo\Api\Http\Response
     */
    public function deleteUsersInGroup(DeleteUsersInGroupRequest $objRequest) {
        try{
            $reqOffice = [
                "group" => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app" => "office"
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                $objAuthGroup = $this->authGroupService->removeUsersFromGroup($objRequest->all());

                return($this->response->item($objAuthGroup, new AuthGroupTransformer(["users", "permissions"])));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @urlParam user required User UUID
     * @responseFile responses/auth/get-groups-user.get.json
     * @param string $user
     * @return \Dingo\Api\Http\Response
     */
    public function getUserGroups(string $user) {
        $reqGroupName = "App.Office.Admin";
        $reqPermissionName = "App.Office.Admin.Default";

        try {
            if(!$this->authService->isAuthorized($reqGroupName, $reqPermissionName)) {
                $objForbiddenPerm = $this->authPermService->findByName($reqPermissionName);

                throw AuthException::userForbidden(Auth::user(), $objForbiddenPerm);
            }

            $objUser = $this->userService->find($user);

            return($this->response->item($objUser, new UserTransformer(["groups"])));
        } catch(\Exception $e) {
            throw $e;
        }
    }

}

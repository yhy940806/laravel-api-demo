<?php

namespace App\Http\Controllers;


use Auth;
use Exception;
use App\Models\User;
use App\Helpers\Client;
use App\Traits\Cacheable;
use Illuminate\Http\Request;
use App\Http\Requests\User\{
    UpdateRequest,
    SecurityRequest,
    UserAliasRequest,
    UserAvatarRequest,
    CreateAccountRequest
};
use App\Facades\Cache\AppCache;
use Illuminate\Support\Facades\Hash;
use App\Repositories\User\UserRepository;
use App\Services\{AuthService, UserService};
use App\Http\Resources\User\DetailCollection;
use App\Http\Requests\Soundblock\{User\AddOriginTeamMemberRequest};
use App\Http\Requests\Office\{User\AutoCompleteRequest, User\UserRequest};
use App\Http\Transformers\User\{UserTransformer, AvatarTransformer, AvatarsTransformer};

/**
 * @group User management
 *
 * APIs for managing users
 */
class UserController extends Controller {
    use Cacheable;

    /** @var AuthService */
    protected AuthService $authService;
    /**
     * @var UserService
     */
    private UserService $userService;
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @param AuthService $authService
     * @param UserService $userService
     * @param UserRepository $userRepository
     */
    public function __construct(AuthService $authService, UserService $userService, UserRepository $userRepository) {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->userRepository = $userRepository;
    }

    /**
     * @responseFile /responses/soundblock/users/index.soundblock.get.json
     */
    public function indexForSoundblock() {
        try {
            $objUser = Auth::user();
            return ($this->response->item($objUser, new UserTransformer(["avatar", "emails"])));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @queryParam user string optional
     * @responseFile responses/users/get-users.get.json
     *
     * @param UserRequest $objRequest
     * @param UserService $userService
     * @return DetailCollection|\Dingo\Api\Http\Response
     * @throws Exception
     */
    public function indexForOffice(UserRequest $objRequest, UserService $userService) {
        try {
            $reqOffice = [
                "group"      => "App.Office.Admin",
                "permission" => "App.Office.Admin.Default",
                "app"        => "office",
            ];

            if ($this->authService->checkAuth($reqOffice)) {
                if ($objRequest->user) {
                    $user = $userService->find($objRequest->user);
                    $user = $userService->getPrimary($user);

                    return ($this->apiReply($user));
                } else {
                    $perPage = intval($objRequest->per_page);
                    $arrUser = User::with(["aliases", "emails", "phones"])->paginate($perPage);

                    return (new DetailCollection($arrUser));
                }
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function addOriginTeamMember(AddOriginTeamMemberRequest $objRequest) {
        try {

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @param UserService $userService
     * @return
     */
    public function store(Request $request, UserService $userService) {
        $objUser = $userService->create($request->all());

        return ($this->apiReply($objUser));
    }

    /**
     * @bodyParam alias string required The user alias
     * @bodyParam email required The user email
     * @bodyParam password string required
     * @bodyParam confirm_password string required
     *
     * @param CreateAccountRequest $request
     * @param UserService $userService
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function createAccount(CreateAccountRequest $request, UserService $userService) {
        try {
            $user = $userService->createAccount($request->all());

            return ($this->response->item($user, new UserTransformer));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $intUser
     * @param UserService $userService
     * @return \Illuminate\Http\Response
     */
    public function show($intUser, UserService $userService) {
        $objUser = $userService->find($intUser);
        return ($this->response->item($objUser, new UserTransformer()));
    }

    /**
     * @queryParam user User Email or Alias.
     * @queryParam select_fields The list of fields that will selected. Fields: name - group_name, memo - group_memo, is_critical - flag_critical, group - group_uuid, auth - auth_uuid. E.g select_fields=name,memo
     * @queryParam select_relations The list of relations that will selected. Accept: emails, aliases. E.g select_relations=emails,aliases
     * @queryParam aliases_fields The list of fields that will selected from alias relation. Fields: alias - user_alias, primary - flag_primary E.g aliases_fields=alias,primary
     * @queryParam emails_fields The list of fields that will selected from email relation. Fields: email - user_auth_email, primary - flag_primary E.g emails_fields=emails,aliases
     *
     * @responseFile responses/office/users/autocomplete.get.json
     * @param AutoCompleteRequest $objRequest
     * @param UserService $userService
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function search(AutoCompleteRequest $objRequest, UserService $userService) {
        try {
            $reqGroupName = "App.Office.Admin";
            $reqPermissionName = "App.Office.Admin.Default";
            if ($this->authService->isAuthorized($reqGroupName, $reqPermissionName)) {
                $arrRelations = [];
                $objUser = $userService->search($objRequest->all());

                if ($objRequest->has("select_relations")) {
                    $arrRelations = explode(",", $objRequest->input("select_relations"));
                }

                return ($this->response->collection($objUser, new UserTransformer($arrRelations, false, null, $objRequest->all())));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @group User management
     * @urlParam user required User UUID.
     * @bodyParam name_first string optional User First Name.
     * @bodyParam name_middle string optional User Middle Name.
     * @bodyParam name_last string optional User Last Name.
     *
     * @param UpdateRequest $objRequest
     * @param UserService $userService
     * @param null $user
     * @return \Illuminate\Http\Response
     * @transformer \App\Http\Transformers\UserTransformer
     * @transformerModel \App\Models\User
     */
    public function update(UpdateRequest $objRequest, UserService $userService, $user = null) {
        if ($objRequest->has("user")) {
            $objUser = $userService->find($objRequest->input("user"), true);
        } else if (isset($user)) {
            $objUser = $userService->find($user, true);
        } else {
            $objUser = Auth::user();
        }

        $objUser = $userService->update($objUser, $objRequest->all());

        return ($this->apiReply($objUser));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $intUser
     * @return \Illuminate\Http\Response
     */
    public function destroy($intUser) {
        $arrUser = $this->userService->find($intUser);
        $this->userService->delete($arrUser);
        return ($this->apiReply());
    }


    /**
     * @group User management
     * @urlParam user required User UUID.
     * @bodyParam alias uuid required Alias Name.
     *
     * @param UserAliasRequest $objRequest
     * @param UserService $userService
     * @param $user
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function addAlias(UserAliasRequest $objRequest, UserService $userService, $user) {
        try {
            $objApp = Client::app();
            $reqGroupName = "App.Office.Admin";
            $reqPermissionName = "App.Office.Admin.Default";

            if ($this->authService->isAuthorized($reqGroupName, $reqPermissionName) && $objApp->app_name == "office") {
                $objUser = $userService->find($user);
                $userService->addAlias($objUser, $objRequest->all());

                return ($this->apiReply($objUser->load(["emails", "aliases"])));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * User Security
     *
     * @group User management
     *
     * @urlParam user required User UUID.
     * @bodyParam old_password string Old Password.
     * @bodyParam password string New Password.
     * @bodyParam password_confirmation string Confirmation of New Password.
     * @bodyParam g2fa bool Flag of enabled 2fa.
     * @bodyParam force_reset bool Flag of forcing resets password.
     *
     * @param SecurityRequest $objRequest
     * @param UserService $userService
     * @param $user
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function security(SecurityRequest $objRequest, UserService $userService, $user) {
        try {
            $objApp = Client::app();

            if (($this->authService->isAuthorized("App.Office.Admin", "App.Office.Admin.Default") && $objApp->app_name == "office") ||
                ($objApp->app_name == "account")) {
                $objUser = $userService->find($user);

                if ($objRequest->has("password")) {
                    if (!Hash::check($objRequest->input("old_password"), $objUser->getAuthPassword())) {
                        throw new \Exception("Old password is not valid", 400);
                    }
                    $objUser = $userService->update($objUser, ["user_password" => $objRequest->input("password")]);
                }

                if ($objRequest->has("g2fa")) {
                    $userService->toggle2FA($objUser, $objRequest->input("g2fa"));
                }

                return ($this->response->item($objUser, new UserTransformer(["emails", "aliases"], false)));
            } else {
                abort(403, "You have not required permission.");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @group User management
     * @bodyParam file file required User Avatar
     * @param UserAvatarRequest $objRequest
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function createAvatar(UserAvatarRequest $objRequest) {
        $user = Auth::user();
        $fileName = $this->userService->addAvatar($user, $objRequest->file("file"));
        $objUser = $this->userRepository->updateUserAvatar($user);

        return ($this->response->item($objUser, new AvatarTransformer()));
    }

    /**
     * @param string $strUserUuid
     * @return \App\Http\Resources\Common\BaseCollection|\Dingo\Api\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function getUserAvatarByUuid(string $strUserUuid) {
        [$isCached, $strAvatarUrl] = $this->userService->getAvatarByUuid($strUserUuid);

        if ($isCached) {
            return response()->json(AppCache::getCache());
        }

        return ($this->sendCacheResponse(response()->json(["user_avatar" => $strAvatarUrl])));
    }

    /**
     * @group User
     * @queryParam uuids required Array of Users UUIDs
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     * @throws Exception
     */
    public function getUsersAvatars(Request $request) {
        $arrUsersUuids = $request->input("uuids");
        $objUsers = $this->userRepository->findAllWhere($arrUsersUuids);

        return ($this->response->collection($objUsers, new AvatarsTransformer));
    }
}

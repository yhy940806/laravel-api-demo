<?php

namespace App\Exceptions;

use Exception;
use App\Models\{Core\Auth\AuthGroup, Core\Auth\AuthPermission, Core\App, User};

class AuthException extends Exception {
    protected User $objUser;

    protected ?AuthGroup $objGroup;

    protected $objApp;

    public function __construct(User $objUser = null, AuthGroup $objGroup = null, AuthPermission $objPerm = null, $message = "", $code = 0, Exception $previous = null) {
        if ($objUser)
            $this->objUser = $objUser;

        if ($objUser)
            $this->objGroup = $objGroup;

        if ($objUser)
            $this->objPerm = $objPerm;

        $code = 417;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param User $objUser
     * @param AuthGroup|null $objGroup
     * @param AuthPermission $objPermission
     * @param Exception|null $previous
     * @return static
     */
    public static function userPermissionsGroupsDenied(User $objUser, AuthGroup $objGroup = null, AuthPermission $objPermission, Exception $previous = null) {
        if (is_null($objGroup)) {
            return (new static($objUser, $objGroup, $objPermission, sprintf("User (%s) permission (%s) denied from group", $objUser->user_uuid, $objPermission->permission_uuid), 417, $previous));

        } else {

            return (new static($objUser, $objGroup, $objPermission, sprintf("User (%s) permission (%s) denied from group (%s)", $objUser->user_uuid, $objPermission->permission_uuid, $objGroup->group_uuid), 417, $previous));

        }

    }

    /**
     * @param User $objUser
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     * @return static
     */
    public static function servicePermissionDenied(User $objUser, $message = "", $code = 417, Exception $previous = null) {
        if ($message == "") {
            $message = sprintf("User (%s) has no serve.", $objUser->user_uuid);
        }
        return (new static($objUser, null, null, $message, 417, $previous));
    }

    /**
     * @param User $objUser
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     * @return static
     */
    public static function hasNotAnyService(User $objUser, $message = "", $code = 417, Exception $previous = null) {
        if ($message == "") {
            $message = sprintf("User (%s) has no service.", $objUser->user_uuid);
        }
        return (new static($objUser, null, null, $message, 417, $previous));
    }

    /**
     * @param User $objUser
     * @param AuthPermission $objPerm
     * @param int $code
     * @param Exception|null $previous
     * @return static
     */
    public static function userForbidden(User $objUser, AuthPermission $objPerm, $code = 417, Exception $previous = null) {
        $message = sprintf("User (%s) is forbidden from permission (%s).", $objUser->user_uuid, $objPerm->permission_uuid);

        return (new static($objUser, null, null, $message, $code, $previous));
    }

    /**
     * @param App $objApp
     * @param int $code
     * @param Exception|null $previous
     * @return static
     */
    public static function appForbidden(App $objApp, $code = 417, Exception $previous = null) {
        $message = sprintf("App (%s) is forbidden from this", $objApp->app_name);
        return (new static(null, null, null, $message, $code, $previous));
    }

    /**
     * @param AuthGroup $objAuthGroup
     * @param AuthPermission $objPerm
     * @param int $code
     * @param Exception|null $previous
     * @return static
     */
    public static function duplicatedPermission(AuthGroup $objAuthGroup, AuthPermission $objPerm, $code = 417, Exception $previous = null) {
        $message = sprintf("Permission (%s) exists in a group (%s) already.", $objAuthGroup->group_uuid, $objPerm->permission_uuid);

        return (new static(null, $objAuthGroup, $objPerm, $message, $code, $previous));
    }

    /**
     * @param User $objUser
     * @param AuthGroup $objAuthGroup
     * @param AuthPermission $objPerm
     * @param int $code
     * @param Exception|null $previous
     * @return static
     */
    public static function userHasPermission(User $objUser, AuthGroup $objAuthGroup, AuthPermission $objPerm, $code = 417, Exception $previous = null) {
        $message = sprintf("User (%s) has permission (%s) already in a group (%s) already.", $objUser->user_uuid, $objPerm->permission_uuid, $objAuthGroup->group_uuid);

        return (new static(null, $objAuthGroup, $objPerm, $message, $code, $previous));
    }

    /**
     * @param User $objUser
     * @param AuthGroup $objAuthGroup
     * @param int $code
     * @param Exception|null $previous
     * @return static
     */
    public static function duplicatedUser(User $objUser, AuthGroup $objAuthGroup, $code = 417, Exception $previous = null) {
        $message = sprintf("User (%s) exists in a group (%s) already.", $objUser->user_uuid, $objAuthGroup->group_uuid);

        return (new static(null, null, null, $message, $code, $previous));
    }

    /**
     * @param AuthGroup $objAuthGroup
     * @param AuthPermission $objAuthPerm
     * @param int $code
     * @param Exception|null $previous
     * @return static
     */
    public static function permissionNoExistsInGroup(AuthGroup $objAuthGroup, AuthPermission $objAuthPerm, $code = 417, Exception $previous = null) {
        $message = sprintf("Permission (%s) not exists in a group (%s).", $objAuthPerm->permission_uuid, $objAuthGroup->group_uuid);

        return (new static(null, $objAuthGroup, $objAuthPerm, $message, 417, $previous));
    }

    public function getUserUUID() {
        return ($this->userUUID);
    }

    public function report() {
        \Log::debug("Permission denied");
    }
}

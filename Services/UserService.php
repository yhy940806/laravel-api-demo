<?php

namespace App\Services;

use Hash;
use Auth;
use Util;
use Exception;
use App\Models\{
    Core\Auth\AuthPermission,
    User,
    UserAuthAlias,
    UserContactEmail
};
use App\Helpers\Constant;
use App\Facades\Cache\AppCache;
use App\Events\Common\VerifyEmail;
use App\Models\Auth\LoginSecurity;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\User\{UserAliasRepository, UserContactEmailRepository, UserRepository};

class UserService {
    /** @var UserRepository */
    protected UserRepository $userRepo;
    /** @var UserAliasRepository */
    protected UserAliasRepository $aliasRepo;
    /** @var UserContactEmailRepository */
    protected UserContactEmailRepository $emailRepo;

    /**
     * @param UserRepository $userRepo
     * @param UserAliasRepository $aliasRepo
     * @param UserContactEmailRepository $emailRepo
     */
    public function __construct(UserRepository $userRepo, UserAliasRepository $aliasRepo, UserContactEmailRepository $emailRepo) {
        $this->userRepo = $userRepo;
        $this->aliasRepo = $aliasRepo;
        $this->emailRepo = $emailRepo;
    }

    /**
     * @param array $arrParams
     * @return User
     */
    public function createAccount(array $arrParams): User {
        $fieldsAlias = config("constant.user.fields_alias");
        $arrUser = [];
        foreach ($fieldsAlias as $key => $value) {
            $arrUser[$value] = $arrParams[$key];
        }

        $arrUser[User::STAMP_CREATED_BY] = 1;
        $arrUser[User::STAMP_UPDATED_BY] = 1;
        $user = $this->create($arrUser);
        $this->createEmail($user, $arrUser["user_auth_email"]);
        $this->createAlias($user, $arrUser["user_alias"]);

        return ($user);
    }

    /**
     * @param array $arrParams
     * @return User
     */
    public function create(array $arrParams): User {
        $arrUser = [];
        $arrUser["user_password"] = Hash::make($arrParams["user_password"]);

        if (isset($arrParams["name_first"])) {
            $arrUser["name_first"] = $arrParams["name_first"];
        }
        if (isset($arrParams["name_middle"])) {
            $arrUser["name_middle"] = $arrParams["name_middle"];
        }
        if (isset($arrParams["name_last"])) {
            $arrUser["name_last"] = $arrParams["name_last"];
        }

        return ($this->userRepo->create($arrUser));
    }

    /**
     * @param User $user
     * @param string $email
     * @return UserContactEmail
     * @throws Exception
     */
    protected function createEmail(User $user, string $email): UserContactEmail {
        if ($this->emailRepo->find($email))
            throw new Exception("This email already exists.", 400);
        $arrEmail = [];
        $arrEmail["user_id"] = $user->user_id;
        $arrEmail["user_uuid"] = $user->user_uuid;
        $arrEmail["user_auth_email"] = $email;
        $arrEmail["flag_primary"] = true;
        $arrEmail[UserContactEmail::STAMP_CREATED_BY] = $user->user_id;
        $arrEmail[UserContactEmail::STAMP_UPDATED_BY] = $user->user_id;
        $email = $this->emailRepo->create($arrEmail);
        event(new VerifyEmail($email));

        return ($email);
    }

    /**
     * @param User $user
     * @param string $alias
     * @return UserAuthAlias
     * @throws Exception
     */
    protected function createAlias(User $user, string $alias): UserAuthAlias {
        if ($this->aliasRepo->find($alias))
            throw new Exception("This alias already exists.", 400);
        $arrAlias = [];
        $arrAlias["user_id"] = $user->user_id;
        $arrAlias["user_uuid"] = $user->user_uuid;
        $arrAlias["flag_primary"] = true;
        $arrAlias["user_alias"] = $alias;
        $arrAlias[UserAuthAlias::STAMP_CREATED_BY] = $user->user_id;
        $arrAlias[UserAuthAlias::STAMP_UPDATED_BY] = $user->user_id;
        return ($this->aliasRepo->create($arrAlias));
    }

    /**
     * @param mixed $id
     * @return bool
     */
    public function delete($id): bool {
        return ($this->userRepo->destroy($id));
    }

    /**
     * @param mixed $where
     * @param $field string
     * @param Collection
     * @return Collection
     * @throws Exception
     *
     */
    public function findAllWhere($where, string $field = "uuid"): Collection {
        return ($this->userRepo->findAllWhere($where, $field));
    }

    public function findByEmailOrAlias($param) {
        $objAuthEmail = $this->emailRepo->find($param);
        if ($objAuthEmail) {
            return ($objAuthEmail->user);
        }

        return ($this->aliasRepo->find($param, true)->user);
    }

    /**
     * @param array $emails
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findAllByEmails(array $emails) {
        return ($this->userRepo->findAllByEmails($emails));
    }

    public function findAllByPermission(AuthPermission $objAuthPerm, int $perPage = 10) {
        return ($this->userRepo->findAllByPermission($objAuthPerm, $perPage));
    }

    public function find($id, $bnFailure = false): User {
        return ($this->userRepo->find($id, $bnFailure));
    }

    public function search(array $arrParams) {
        return ($this->userRepo->search($arrParams));
    }

    /**
     * @param string $newPassword
     * @param \App\Models\User $user
     *
     * @return \App\Models\User
     */
    public function changePassword(string $newPassword, $user = null) {
        if (is_null($user)) {
            /** @var \App\Models\User */
            $user = Auth::user();
        }
        $arrParams["user_password"] = $newPassword;

        return ($this->update($user, $arrParams));
    }

    public function update(User $objUser, array $arrParams): User {
        $arrUser = [];

        if (isset($arrParams["user_password"])) {
            $arrUser["user_password"] = Hash::make($arrParams["user_password"]);
        }

        if (isset($arrParams["remember_token"])) {
            $arrUser["remember_token"] = Util::remember_token();
        }

        if (isset($arrParams["name_first"])) {
            $arrUser["name_first"] = $arrParams["name_first"];
        }

        if (isset($arrParams["name_middle"])) {
            $arrUser["name_middle"] = $arrParams["name_middle"];
        }

        if (isset($arrParams["name_last"])) {
            $arrUser["name_last"] = $arrParams["name_last"];
        }
        return ($this->userRepo->update($objUser, $arrUser));
    }

    /**
     * @param User $user
     * @param array $load
     */
    public function getPrimary(User $user, array $load = ["aliases", "phones"]) {
        return ($this->userRepo->getPrimary($user, $load));
    }

    /**
     * @param User $objUser
     * @param array $arrParams
     */
    public function addAlias(User $objUser, array $arrParams): void {
        $objUser->aliases()->create([
            "alias_uuid"   => Util::uuid(),
            "user_uuid"    => $objUser->user_uuid,
            "user_alias"   => $arrParams["alias"],
            "flag_primary" => false,
        ]);
    }

    /**
     * Enable/Disable 2FA
     *
     * @param User $objUser
     * @param bool $g2faStatus
     * @return LoginSecurity|null
     */
    public function toggle2FA(User $objUser, bool $g2faStatus): ?LoginSecurity {
        /** @var LoginSecurity $objSecurity */
        $objSecurity = $objUser->loginSecurity;

        if ($g2faStatus) {
            if (is_null($objSecurity)) {
                $google2fa = app("pragmarx.google2fa");

                $objUser->passwordsecurity()->create([
                    "row_uuid"         => Util::uuid(),
                    "user_id"          => $objUser->user_id,
                    "user_uuid"        => $objUser->user_uuid,
                    "google2fa_enable" => true,
                    "google2fa_secret" => $google2fa->generateSecretKey(),
                ]);
            } else {
                $objSecurity->update([
                    "google2fa_enable" => true,
                ]);
            }
        } else {
            if (isset($objSecurity)) {
                $objSecurity->update([
                    "google2fa_enable" => false,
                ]);
            }
        }


        return $objSecurity;
    }

    /**
     * @param $objUser
     * @param $objFile
     * @return string
     * @throws Exception
     */
    public function addAvatar($objUser, $objFile) {
        try {
            $fileName = $objUser->user_uuid . ".png";
            $temp = tempnam(null, null);
            $path = "public" . Constant::Separator . "users" . Constant::Separator . "avatars";

            imagepng(imagecreatefromstring(file_get_contents($objFile)), $temp);

            Storage::disk("s3-account")->putFileAs($path, $temp, $fileName, "public");

            return ($fileName);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @param string $strUserUuid
     * @return array
     */
    public function getAvatarByUuid(string $strUserUuid) {
        AppCache::setCacheKey(self::class . ".getAvatarByUuid.User.Avatar.{$strUserUuid}");

        if (AppCache::isCached()) {
            return ([true, null]);
        }

        $objUser = $this->userRepo->getUserByUUID($strUserUuid);
        $path = $objUser->flag_avatar ?
            "users" . Constant::Separator . "avatars" . Constant::Separator . $strUserUuid . ".png" :
            "users" . Constant::Separator . "avatars" . Constant::Separator . "default.png";

        return ([false, $path]);
    }
}

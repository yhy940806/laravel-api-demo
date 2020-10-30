<?php

namespace App\Services;

use Util;
use Auth;
use Exception;
use App\Helpers\Client;
use App\Events\Common\VerifyEmail;
use App\Models\{User, UserContactEmail};
use Illuminate\Support\Facades\{Hash, Mail};
use Illuminate\Database\Eloquent\Collection;
use App\Mail\Soundblock\EmailVerificationMail;
use App\Repositories\User\{UserContactEmailRepository, UserRepository};
use Symfony\Component\HttpKernel\Exception\{BadRequestHttpException, NotFoundHttpException};

class EmailService {
    /** @var UserRepository */
    protected UserRepository $userRepo;
    /** @var UserContactEmailRepository */
    protected UserContactEmailRepository $emailRepo;
    /**
     * @var UserService
     */
    private UserService $userService;

    /**
     * @param UserRepository $userRepo
     * @param UserContactEmailRepository $emailRepo
     * @param UserService $userService
     */
    public function __construct(UserRepository $userRepo, UserContactEmailRepository $emailRepo, UserService $userService) {
        $this->userRepo = $userRepo;
        $this->emailRepo = $emailRepo;
        $this->userService = $userService;
    }

    public function findByUser($user, int $perPage = 5) {
        if ($user instanceof User) {
            $objUser = $user;
        } else if (Util::is_uuid($user) || is_int($user)) {
            $objUser = $this->userService->find($user);
        } else {
            throw new Exception();
        }

        return ($objUser->emails()->paginate($perPage));
    }

    /**
     * @param string $email
     * @param User|null $objUser
     * @param bool $forceVerified
     * @return UserContactEmail
     * @throws Exception
     */
    public function create(string $strEmail, ?User $objUser = null, $forceVerified = false): UserContactEmail {
        $arrEmail = [];

        if (is_null($objUser)) {
            $objUser = Auth::user();
        }

        /** @var UserContactEmail $objEmail */
        $objEmail = $this->findWithTrashed($strEmail);

        if (is_object($objEmail) && $objEmail->trashed()) {
            $objEmail->restore();

            return $objEmail;
        } else if (is_object($objEmail)) {
            throw new Exception("Email exists already.");
        }

        $arrEmail["user_id"] = $objUser->user_id;
        $arrEmail["user_uuid"] = $objUser->user_uuid;
        $arrEmail["user_auth_email"] = $strEmail;

        if (!$this->primary($objUser)) {
            $arrEmail["flag_primary"] = true;
        } else {
            $arrEmail["flag_primary"] = false;
        }
        $email = $this->emailRepo->create($arrEmail);
        if ($forceVerified) {
            $email->verified();
        }
        return($email);
    }

    public function findWithTrashed(string $strEmail) {
        return ($this->emailRepo->findWithTrashed($strEmail));
    }

    public function initForPrimary(User $objUser) {
        $arrEmails = $objUser->emails()->get();

        $arrEmails->transform(function ($objEmail, $key) {
            $objEmail->update(["flag_primary" => false]);
        });
    }

    public function findForUser(User $objUser, string $emailUuid) {
        return $objUser->emails()->where("row_uuid", $emailUuid)->first();
    }

    public function findForUserByEmail(User $objUser, string $email) {
        return $objUser->emails()->where("user_auth_email", $email)->first();
    }

    /**
     * @param UserContactEmail $objEmail
     * @return UserContactEmail
     */
    public function sendVerificationEmail(UserContactEmail $objEmail): UserContactEmail {
        $objEmail->verification_hash = hash("ripemd160", Hash::make($objEmail->user_auth_email));
        $objEmail->save();

        Mail::to($objEmail->user_auth_email)->send(new EmailVerificationMail($objEmail, Client::app()));

        return $objEmail;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function hasPrimary(User $user): bool {
        return ($this->emailRepo->hasPrimary($user));
    }

    /**
     * @param User $user
     * @return UserContactEmail|null
     */
    public function primary(User $user): ?UserContactEmail {
        return ($this->emailRepo->primary($user));
    }

    /**
     * @param User $user
     * @return Collection
     */
    public function verifiedEmails(User $user): Collection {
        return ($this->emailRepo->verifiedEmails($user));
    }

    /**
     * @param mixed $user
     * @return array
     * @throws Exception
     */
    public function getRecpients($user): array {
        $recpients = [];
        $users = collect();

        if ($user instanceof User) {
            $users->push($user);
        } else if ($user instanceof Collection) {
            $users = $user;
        } else {
            throw new Exception("Invalid Paramter.", 400);
        }

        foreach ($users as $user) {
            if ($user->recpient) {
                array_push($recpients, $user->recpient);
            }
        }

        return ($recpients);
    }

    public function delete(string $strEmail, User $objUser = null) {
        $objEmail = $this->find($strEmail, true);

        if (is_null($objUser)) {
            $objUser = Auth::user();
        }

        if ($objEmail->flag_primary) {
            throw new BadRequestHttpException("You can't remove primary email.");
        }

        if ($this->userHasEmail($objUser, $objEmail)) {
            return ($objEmail->delete());
        }

        throw new Exception("User has n't this email");
    }

    public function find(string $strEmail, bool $bnFailure = false) {
        return ($this->emailRepo->find($strEmail, $bnFailure));
    }

    public function userHasEmail(User $objUser, UserContactEmail $objEmail) {
        return ($objUser->emails()->whereRaw("lower(user_auth_email) = ?", Util::lowerLabel($objEmail->user_auth_email))
                        ->exists());
    }

    /**
     * @param User $user
     * @param string $hash
     * @return UserContactEmail
     */
    public function verifyEmailByHash(string $hash): UserContactEmail {
        $objEmail = $this->emailRepo->getEmailByVerificationHash($hash);

        if (is_null($objEmail)) {
            throw new NotFoundHttpException("Email Not Found.");
        }

        if ($objEmail->flag_verified) {
            throw new BadRequestHttpException("This email has already been verified.");
        }

        return $this->verifyEmail($objEmail);
    }

    public function verifyEmail(UserContactEmail $objEmail) {
        return $this->emailRepo->verifyEmail($objEmail);
    }

    public function update(UserContactEmail $objEmail, User $objUser = null, array $arrParams): UserContactEmail {
        $arrEmail = [];
        if (is_null($objUser))
            $objUser = Auth::user();

        if (isset($arrParams["user_auth_email"])) {
            if (!$this->userHasEmail($objUser, $objEmail)) {
                throw new Exception("User has not this email.");
            }
            $arrEmail["user_auth_email"] = $arrParams["user_auth_email"];
        }

        if (isset($arrParams["flag_primary"]) && $arrParams["flag_primary"]) {
            $this->initForPrimary($objUser);
            $arrEmail["flag_primary"] = true;

        } else if (isset($arrParams["flag_primary"])) {
            $arrEmail["flag_primary"] = false;
        }

        $objEmail = $this->emailRepo->update($objEmail, $arrEmail);

        if (isset($arrParams["user_auth_email"]))
            event(new VerifyEmail($objEmail));

        return ($objEmail);
    }
}

<?php

namespace App\Models;

use App\Models\Common\QueueJob;
use Auth;
use Illuminate\{Support\Arr,
    Notifications\Notifiable,
    Database\Eloquent\SoftDeletes,
    Foundation\Auth\User as Authenticatable
};
use Laravel\{Passport\HasApiTokens};
use App\Traits\FinanceBillable;
use App\Models\Soundblock\{Contract, ProjectNote, Service, ServiceNote, ServiceTransaction, Team};
use App\Models\{Auth\LoginSecurity,
    Accounting\AccountingInvoice,
    Core\App,
    Core\Auth\AuthGroup,
    Core\Auth\AuthPermission,
    Office\Contact,
    Casts\StampCast};
use Util;

class User extends Authenticatable {
    use Notifiable;
    use HasApiTokens;
    use SoftDeletes;
    use FinanceBillable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    const CREATED_AT = "stamp_created_at";
    const STAMP_CREATED = "stamp_created";
    const STAMP_CREATED_BY = "stamp_created_by";

    const UPDATED_AT = "stamp_updated_at";
    const STAMP_UPDATED = "stamp_updated";
    const STAMP_UPDATED_BY = "stamp_updated_by";

    const DELETED_AT = "stamp_deleted_at";
    const STAMP_DELETED = "stamp_deleted";
    const STAMP_DELETED_BY = "stamp_deleted_by";
    const STRIPE_FIELDS = [
        "row_id", "row_uuid", "stripe_id", "card_brand", "card_last_four", "trial_ends_at",
    ];
    protected $primaryKey = "user_id";
    protected $uuid = "user_uuid";
    protected $guarded = [];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        "user_id", "user_password", "remember_token", "name_first", "name_middle", "name_last",
        User::DELETED_AT, User::STAMP_DELETED, User::STAMP_DELETED_BY,
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        "pivot",
    ];
    protected $appends = [
        "name", "primary_email",
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        BaseModel::STAMP_CREATED_BY => StampCast::class,
        BaseModel::STAMP_UPDATED_BY => StampCast::class,
    ];
    /**
     * @var UserAccountingStripe|null $stripeModel
     */
    private $stripeModel = null;

    protected static function boot() {
        parent::boot();

        static::creating(function ($model) {
            $model->{static::STAMP_CREATED} = time();
            $model->{static::STAMP_UPDATED} = time();
            if (Auth::user()) {
                $model->{static::STAMP_CREATED_BY} = Auth::id();
                $model->{static::STAMP_UPDATED_BY} = Auth::id();

            } else {
                $model->{static::STAMP_CREATED_BY} = 1;
                $model->{static::STAMP_UPDATED_BY} = 1;

                // Cache User
            }
        });

        static::updating(function ($model) {

            $model->stamp_updated = time();
            if (Auth::user()) {
                $model->stamp_updated_by = Auth::id();

            } else {
                $model->stamp_updated_by = 1;
            }
        });
    }

    public function uuid() {
        return ($this->uuid);
    }

    public function loginSecurity() {
        return ($this->hasOne(LoginSecurity::class, "user_id", "user_id"));
    }

    public function getJWTIdentifier() {
        return ($this->getKey());
    }

    public function getAuthPassword() {
        return ($this->user_password);
    }

    public function getJWTCustomClaims() {
        return ([]);
    }

    public function findForPassport($identify) {
        $identify = strtolower($identify);

        $objAlias = UserAuthAlias::whereRaw("lower(user_alias) = (?)", $identify)->first();
        if ($objAlias) {
            $user = $objAlias->user;
            $isVerified = $user->emails()->where("flag_verified", true)->exists();
            return ($isVerified ? $user : null);
        } else {
            $userEmail = UserContactEmail::whereRaw("lower(user_auth_email) =(?)", $identify)
                                        ->where("flag_verified", true)->first();
            if ($userEmail) {
                $user = $userEmail->user;
            } else {
                $user = null;
            }
        }

        return ($user);
    }

    public function contact() {
        return ($this->hasOne("App\UserContact", "user_uuid", "contact_uuid"));
    }

    public function contracts() {
        return ($this->belongsToMany(Contract::class, "soundblock_projects_contracts_users", "user_id", "contract_id", "user_id", "contract_id")
                     ->whereNull("soundblock_projects_contracts_users." . BaseModel::STAMP_DELETED)
                     ->withPivot("contract_uuid", "user_uuid", "user_payout", "contract_status")
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function paymethods() {
        return ($this->hasMany("App\Model\UserSoundBlockPaymethods", "user_uuid", "paymethod_uuid"));
    }

    public function teams() {
        return ($this->belongsToMany(Team::class, "soundblock_projects_teams_users", "user_id", "team_id", "user_id", "team_id")
                     ->whereNull("soundblock_projects_teams_users." . BaseModel::STAMP_DELETED)
                     ->withPivot("team_uuid", "user_uuid", "user_payout", "user_role")
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function service() {
        return ($this->hasOne(Service::class, "user_id", "user_id"));
    }

    public function groups() {
        return ($this->belongsToMany(AuthGroup::class, "core_auth_groups_users", "user_id", "group_id", "user_id", "group_id")
                     ->whereNull("core_auth_groups_users." . BaseModel::STAMP_DELETED)
                     ->withPivot("app_id", "app_uuid")
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function groupsWithPermissions() {
        return ($this->belongsToMany(AuthGroup::class, "core_auth_permissions_groups_users", "user_id", "group_id", "user_id", "group_id")
                     ->whereNull("core_auth_permissions_groups_users." . BaseModel::STAMP_DELETED)
                     ->withPivot("permission_id", "permission_uuid", "permission_value")
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function groupsWithPermissionsWithTrashed() {
        return ($this->belongsToMany(AuthGroup::class, "core_auth_permissions_groups_users", "user_id", "group_id", "user_id", "group_id")
                     ->withPivot("permission_id", "permission_uuid", "permission_value")
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function permissionsInGroup() {
        return ($this->belongsToMany(AuthPermission::class, "core_auth_permissions_groups_users", "user_id", "permission_id", "user_id", "permission_id")
                     ->whereNull("core_auth_permissions_groups_users." . BaseModel::STAMP_DELETED)
                     ->withPivot("group_id", "group_uuid", "permission_value")
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function permissionsInGroupWithTrashed() {
        return ($this->belongsToMany(AuthPermission::class, "core_auth_permissions_groups_users", "user_id", "permission_id", "user_id", "permission_id")
                     ->withPivot("group_id", "group_uuid", "permission_value")
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function avatar() {
        return ($this->hasMany(UserAvatar::class, "user_id", "user_id"));
    }

    public function correspondences() {
        return ($this->hasMany(UserCorrespondence::class, "user_id", "user_id"));
    }

    public function postals() {
        return ($this->hasMany(UserContactPostal::class, "user_id", "user_id"));
    }

    public function apps() {
        return ($this->belongsToMany(App::class, "users_auth_apps", "user_id", "app_id", "user_id", "app_id")
                     ->withPivot(BaseModel::VISITED_AT, BaseModel::STAMP_VISITED, BaseModel::STAMP_VISITED_BY)
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function notifications() {
        return ($this->belongsToMany(Notification::class, "notifications_users", "user_id", "notification_id", "user_id", "notification_id")
                     ->whereNull("notifications_users." . BaseModel::STAMP_DELETED)
                     ->withPivot("notification_uuid", "user_uuid", "notification_state", "flag_canarchive", "flag_candelete", "flag_email")
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function notificationSettings() {
        return ($this->hasMany(NotificationSetting::class, "user_id", "user_id"));
    }

    public function transactions() {
        return ($this->hasMany(ServiceTransaction::class, "user_id", "user_id"));
    }

    public function paypals() {
        return ($this->hasMany(AccountingPaypal::class, "user_id", "user_id"));
    }

    public function bankings() {
        return ($this->hasMany(AccountingBanking::class, "user_id", "user_id"));
    }

    public function project_notes() {
        return ($this->hasMany(ProjectNote::class, "user_id", "user_id"));
    }

    public function service_notes() {
        return ($this->hasMany(ServiceNote::class, "user_id", "user_id"));
    }

    public function notes() {
        return ($this->hasMany(UserNote::class, "user_id", "user_id"));
    }

    public function jobs() {
        return ($this->hasMany(QueueJob::class, "user_id", "user_id"));
    }

    public function contacts() {
        return ($this->hasMany(Contact::class, "user_id", "user_id"));
    }

    public function access_contacts() {
        return ($this->belongsToMany(Contact::class, "office_contact_users", "user_id", "contact_id", "user_id", "contact_id")
                     ->whereNull("office_contact_users." . BaseModel::STAMP_DELETED)
                     ->withPivot("contact_uuid", "user_uuid")
                     ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function userInvoices() {
        return $this->hasMany(AccountingInvoice::class, "user_id", "user_id");
    }

    public function getNameAttribute() {
        $fullName = "";

        if ($this->name_first) {
            $fullName = $this->name_first;
        }
        if ($this->name_last) {
            $fullName = $fullName . " " . $this->name_last;
        }

        return ($fullName);
    }

    public function getPrimaryEmailAttribute() {
        /** @var UserContactEmail */
        $primaryEmail = $this->emails()->where("flag_primary", true)->first();
        if (is_null($primaryEmail)) {
            $primaryEmail = $this->emails()->first();
        }

        return ($primaryEmail);
    }

    public function emails() {
        return ($this->hasMany(UserContactEmail::class, "user_id", "user_id"));
    }

    public function getPrimaryAliasAttribute() {
        /** @var UserAuthAlias */
        $primaryAlias = $this->aliases()->where("flag_primary", true)->first();
        if (is_null($primaryAlias)) {
            $primaryAlias = $this->aliases()->first();
        }

        return ($primaryAlias);
    }

    public function aliases() {
        return ($this->hasMany(UserAuthAlias::class, "user_id", "user_id"));
    }

    public function getPrimaryPhoneAttribute() {
        /** @var UserContactPhone */
        $primaryPhone = $this->phones()->where("flag_primary", true)->first();
        if (is_null($primaryPhone)) {
            $primaryPhone = $this->phones()->first();
        }

        return ($primaryPhone);
    }

    public function phones() {
        return ($this->hasMany(UserContactPhone::class, "user_id", "user_id"));
    }

    public function getAvatarAttribute() {
        return (Util::avatar_url($this));
    }

    public function passwordReset() {
        return $this->hasMany(PasswordReset::class, "user_id", "user_id");
    }

    public function recpient() {
        $primaryEmail = $this->primary_email;
        return ([
            [
                "email" => $primaryEmail->user_auth_email,
                "name"  => $this->name,
            ],
        ]);
    }

    public function __get($key) {
        if (array_search($key, self::STRIPE_FIELDS) !== false) {
            $stripe = $this->stripe()->first();

            return is_null($stripe) ? null : $stripe->$key;
        }

        return parent::__get($key);
    }

    public function __set($key, $value) {
        if (array_search($key, self::STRIPE_FIELDS) !== false) {
            $stripe = $this->stripe()->first();

            if (is_null($stripe)) {
                $stripe = $this->stripe()->newRelatedInstanceFor($this);
            }

            if (is_null($this->stripeModel)) {
                $this->stripeModel = $stripe;
            }

            $this->stripeModel->$key = $value;
        } else {
            parent::__set($key, $value);
        }
    }

    public function stripe() {
        return $this->hasOne(UserAccountingStripe::class, "user_id", "user_id");
    }

    public function save(array $options = []) {
        if (isset($this->stripeModel)) {
            return $this->stripeModel->save($options);
        } else {
            return parent::save($options);
        }
    }

    /**
     * Fill the model with an array of attributes. Force mass assignment.
     *
     * @param array $attributes
     */
    public function forceFill(array $attributes) {
        if (Arr::has(array_flip(self::STRIPE_FIELDS), array_keys($attributes))) {
            return $this->stripe->unguarded(function () use ($attributes) {
                return $this->stripe->fill($attributes);
            });
        } else {
            return parent::forceFill($attributes);
        }
    }
}

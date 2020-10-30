<?php

namespace App\Http\Transformers\User;

use App\Helpers\Client;
use League\Fractal\TransformerAbstract;
use App\Models\{Core\Auth\AuthPermission, User, BaseModel};
use App\Http\Transformers\{Auth\AuthGroupTransformer,
    Auth\AuthPermissionTransformer,
    Auth\OnlyAuthGroupTransformer,
    Soundblock\ContractTransformer};
use App\Traits\StampCache;

class UserTransformer extends TransformerAbstract
{
    use StampCache;

    public $availableIncludes = [

    ];

    protected $defaultIncludes = [

    ];

    protected $primaryonly;

    protected $objPerm;
    /**
     * @var null
     */
    private $arrIncludes;
    /**
     * @var array|null
     */
    private $arrFields;
    /**
     * @var \App\Models\Core\App
     */
    private $app;
    /**
     * @var array
     */
    private $pivotKeys;
    /**
     * @var string|null
     */
    private $pivotName;

    /**
     * UserTransformer constructor.
     * @param null $arrIncludes
     * @param bool $primaryonly
     * @param AuthPermission|null $objPerm
     * @param array|null $arrFields
     * @param array $options
     */
    public function __construct($arrIncludes = null, bool $primaryonly = true, ?AuthPermission $objPerm = null,
                                ?array $arrFields = null, array $options = []) {
        $this->primaryonly = $primaryonly;
        $this->objPerm = $objPerm;

        if ($arrIncludes)
        {
            foreach($arrIncludes as $item)
            {
                $item = strtolower($item);
                $this->availableIncludes []= $item;
                $this->defaultIncludes []= $item;
            }
        }

        $this->arrIncludes = $arrIncludes;
        $this->arrFields = $arrFields;
        $this->app = Client::app();

        foreach($options as $optionName => $optionValue) {
            $this->{$optionName} = $optionValue;
        }
    }

    public function transform(User $objUser)
    {
        $response = [
            "user_uuid" => $objUser->user_uuid,
            "name" => $objUser->name,
            "avatar" => $objUser->avatar
        ];

        if(isset($objUser->pivot) && !empty($this->pivotKeys)) {
            $pivotKey = $this->pivotName ?? "pivot";

            $response[$pivotKey] = $objUser->pivot->only($this->pivotKeys);

            if(isset($response[$pivotKey]["user_role"])){
                $response["user_role"] = $response[$pivotKey]["user_role"];
                unset($response[$pivotKey]["user_role"]);
            }
        }

        $stamps = $this->stamp($objUser);
        if(isset($this->arrFields["select_fields"])) {
            $arrFieldsAliasMap = config("constant.autocomplete.users.fields_alias");
            $arrSelect = collect($arrFieldsAliasMap)->only($this->arrFields["select_fields"])->values()->all();
            $response = collect($response)->only($arrSelect)->all();
        }

        return array_merge($response, $stamps);
    }

    public function includeAliases(User $objUser) {
        $fields = [];

        if(isset($this->arrFields["aliases_fields"])) {
            $fields = explode(",", $this->arrFields["aliases_fields"]);
            $alias = config('constant.autocomplete.users.fields_alias.relations.aliases');
            $fields = collect($alias)->only($fields)->values()->all();
        }

        if ($objUser->aliases) {
            if ($this->primaryonly) {
                return ($this->item($objUser->aliases()->where("flag_primary", true)->first(), new AuthAliasTransformer($fields)));
            } else {
                return ($this->collection($objUser->aliases, new AuthAliasTransformer($fields)));
            }
        }

    }

    public function includeEmails(User $objUser) {
        $fields = [];

        if(isset($this->arrFields["emails_fields"])) {
            $fields = explode(",", $this->arrFields["emails_fields"]);
            $alias = config('constant.autocomplete.users.fields_alias.relations.emails');
            $fields = collect($alias)->only($fields)->values()->all();
        }

        if ($objUser->emails) {
            if ($this->primaryonly) {
                $query =  $objUser->emails()->where("flag_primary", true);
                return($this->item($query->first(), new EmailTransformer($fields)));
            } else {
                $query = $objUser->emails();
                return($this->collection($query->get(), new EmailTransformer($fields)));
            }
        }
    }

    public function includePhones(User $objUser) {
        if ($this->primaryonly) {
            return($this->item($objUser->phones()->where("flag_primary", true)->first(), new PhoneTransformer));
        } else {
            return($this->collection($objUser->phones, new PhoneTransformer));
        }

    }

    public function includePostals(User $objUser) {
        return($this->collection($objUser->postals, new PostalTransformer));
    }

    public function includePermissionsInGroup(User $objUser) {
        if (!$this->objPerm) {
            return($this->collection($objUser->permissionsInGroup, new AuthPermissionTransformer));
        } else {
            return($this->collection($objUser->permissionsInGroup()
                        ->wherePivot("permission_id", $this->objPerm->permission_id)
                        ->get(), new AuthPermissionTransformer));
        }

    }

    public function includeGroupsWithPermissions(User $objUser)
    {
        if (!$this->objPerm)
        {
            return($this->collection($objUser->groupsWithPermissions, new OnlyAuthGroupTransformer));
        } else {
            return($this->collection($objUser->groupsWithPermissions()
                                            ->wherePivot("permission_id", $this->objPerm->permission_id)
                                            ->get(), new AuthGroupTransformer));
        }
    }

    public function includePaypals(User $objUser)
    {
        return($this->collection($objUser->paypals, new PaypalTransformer));
    }

    public function includeBankings(User $objUser)
    {
        return($this->collection($objUser->bankings, new BankingTransformer));
    }

    public function includeContracts(User $objUser)
    {
        return($this->collection($objUser->contracts, new ContractTransformer));
    }

    public function includeGroups(User $objUser) {
        return $this->collection($objUser->groups, new AuthGroupTransformer(["permissions"]));
    }
}

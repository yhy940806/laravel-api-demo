<?php

namespace App\Http\Transformers\User;

use App\Http\Transformers\BaseTransformer;
use App\Models\BaseModel;
use App\Models\User;
use App\Models\UserAuthAlias;
use App\Traits\StampCache;
use Cache;

class AuthAliasTransformer extends BaseTransformer
{
    use StampCache;
    /**
     * @var array
     */
    private $allowedFields;

    /**
     * AuthAliasTransformer constructor.
     * @param array $allowedFields
     * @param array|null $arrIncludes
     * @param string $resType
     */
    public function __construct(array $allowedFields = [], array $arrIncludes = null, $resType = "soundblock") {
        parent::__construct($arrIncludes, $resType);
        $this->allowedFields = $allowedFields;
    }

    /**
     * @param UserAuthAlias|null $objUserAuthAlias
     * @return array
     */
    public function transform(?UserAuthAlias $objUserAuthAlias)
    {
        if(is_null($objUserAuthAlias)) {
            return [];
        }

        $response = [
            "user_alias" => $objUserAuthAlias->user_alias,
            "flag_primary" => $objUserAuthAlias->flag_primary
        ];

        if(!empty($this->allowedFields)) {
            $response = collect($response)->only($this->allowedFields)->all();
        }
        $stamps = $this->stamp($objUserAuthAlias);

        return(array_merge($response, $stamps));
    }
}

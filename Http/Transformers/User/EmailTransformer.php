<?php

namespace App\Http\Transformers\User;

use App\Http\Transformers\BaseTransformer;
use App\Models\UserContactEmail;
use App\Traits\StampCache;

class EmailTransformer extends BaseTransformer {

    use StampCache;

    /**
     * @var array|null
     */
    private $allowedFields;

    /**
     * EmailTransformer constructor.
     * @param array|null $allowedFields
     * @param array|null $arrIncludes
     * @param string $resType
     */
    public function __construct(array $allowedFields = [], array $arrIncludes = null, $resType = "soundblock") {
        parent::__construct($arrIncludes, $resType);
        $this->allowedFields = $allowedFields;
    }

    /**
     * @param UserContactEmail|null $objEmail
     * @return array
     */
    public function transform(?UserContactEmail $objEmail) {
        if (is_null($objEmail)) {
            return [];
        }

        $response = [
            "email_uuid"      => $objEmail->row_uuid,
            "user_auth_email" => $objEmail->user_auth_email,
            "flag_primary"    => $objEmail->flag_primary,
            "flag_verified"   => $objEmail->flag_verified,
        ];

        if (!empty($this->allowedFields)) {
            $response = collect($response)->only($this->allowedFields)->all();
        }

        $stamps = $this->stamp($objEmail, "email");

        return (array_merge($response, $stamps));
    }
}

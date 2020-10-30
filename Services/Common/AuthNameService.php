<?php

namespace App\Services\Common;

use App\Models\Core\Auth\AuthModel;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class AuthNameService {

    public function __construct() {

    }

    public function find($id) {
        if (is_int($id)) {
            return (AuthModel::findOrFail($id));
        } else if (is_string($id)) {
            return (AuthModel::where("auth_uuid", $id)->firstOrFail());
        } else {
            throw new InvalidParameterException();
        }
    }

    public function findOneByName($strName) {
        return (AuthModel::where("auth_name", $strName)->firstOrFail());
    }
}

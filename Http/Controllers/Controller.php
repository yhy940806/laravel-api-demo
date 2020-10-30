<?php

namespace App\Http\Controllers;

use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use Helpers;

    public function apiReply($objData = null, string $strMessage = "", int $intStatus = Response::HTTP_OK) {
        return ($this->respond($objData, $strMessage, $intStatus));
    }

    public function respond($objData, string $strMessage, int $intStatus = Response::HTTP_OK) {
        return ($this->response->array([
            "data"   => $objData,
            "status" => [
                "app"     => "Arena.API",
                "code"    => $intStatus,
                "message" => $strMessage,
            ],
        ])->setStatusCode($intStatus));
    }

    public function apiReject($objData = null, string $strMessage = "", int $intStatus = Response::HTTP_EXPECTATION_FAILED) {
        return ($this->respond($objData, $strMessage, $intStatus));
    }

}

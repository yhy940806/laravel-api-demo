<?php

namespace App\Traits;

use Response as HttpResponse;
use Dingo\Api\Http\Response as DingoResponse;

trait Response {

    /**
     * @param mixed $objData
     * @param string $strMessage
     * @param int $intStatus
     * @return HttpResponse
     */
    public function failure($objData = null, string $strMessage = "", $intStatus = DingoResponse::HTTP_EXPECTATION_FAILED) {
        return ($this->respond($objData, $strMessage, $intStatus));
    }

    /**
     * @param mixed $objData
     * @param string $strMessage
     * @param int $intStatus
     * @return HttpResponse
     */
    public function respond($objData, string $strMessage, int $intStatus = DingoResponse::HTTP_OK) {
        return (HttpResponse::json([
            "response" => $objData,
            "status"   => [
                "app"     => "Arena.API",
                "code"    => $intStatus,
                "message" => $strMessage,
            ]], $intStatus));
    }
}

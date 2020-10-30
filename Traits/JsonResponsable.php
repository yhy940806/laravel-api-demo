<?php

namespace App\Traits;

use Dingo\Api\Http\Response;

trait JsonResponsable {
    /**
     * @var int
     */
    protected static $intStatus = Response::HTTP_OK;
    /**
     * @var string
     */
    protected static $strMessage = "";

    /**
     * @param int $intStatus
     * @param string $strMessage
     *
     * @return void
     */
    public static function setStatus($intStatus = Response::HTTP_OK, $strMessage = "") {
        static::$intStatus = $intStatus;
        static::$strMessage = $strMessage;
    }

    /**
     * Customize the response for a request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\JsonResponse $response
     * @return void
     */
    public function withResponse($request, $response) {
        $response->setStatusCode(static::$intStatus);
        parent::withResponse($request, $response);
    }

    protected function setStatusData() {
        $this->additional(array_merge($this->additional, [
            "status" => [
                "app"     => "Arena.API",
                "code"    => static::$intStatus,
                "message" => static::$strMessage,
            ],
        ]));
    }
}

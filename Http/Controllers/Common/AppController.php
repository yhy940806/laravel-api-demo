<?php

namespace App\Http\Controllers\Common;

use App\Services\Common\AppService;
use App\Http\Controllers\Controller;
use App\Http\Transformers\Common\AppTransformer;

class AppController extends Controller {
    //
    /**
     * @param AppService $appService
     * @return \Dingo\Api\Http\Response
     */
    public function index(AppService $appService) {
        $arrApp = $appService->findAll();
        return ($this->response->collection($arrApp, new AppTransformer));
    }
}

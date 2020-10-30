<?php

namespace App\Http\Controllers\Apparel;

use App\Http\Controllers\Controller;
use App\Services\Apparel\FileService;

class FilesController extends Controller
{
    /**
     * @group Apparel
     * @urlParam file required UUID of file
     *
     * @param string $file
     *
     * @param FileService $fileService
     * @return \Dingo\Api\Http\Response
     */
    public function getFileUrl(string $file, FileService $fileService){
        return($this->apiReply($fileService->find($file)));
    }
}

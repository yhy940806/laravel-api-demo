<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\Storage;
use App\Repositories\Common\AppRepository;
use App\Repositories\Core\AppsPagesRepository;

class AppsPagesService {
    /**
     * @var AppsPagesRepository
     */
    private AppsPagesRepository $pagesRepository;
    /**
     * @var AppRepository
     */
    private AppRepository $appRepository;

    /**
     * PagesService constructor.
     * @param AppsPagesRepository $pagesRepository
     * @param AppRepository $appRepository
     */
    public function __construct(AppsPagesRepository $pagesRepository, AppRepository $appRepository) {
        $this->appRepository   = $appRepository;
        $this->pagesRepository = $pagesRepository;
    }

    /**
     * @param string $appName
     * @param string $pageURL
     * @param string|null $pageUrlParams
     * @return mixed
     */
    public function getPageByURL(string $appName, string $pageURL, ?array $pageUrlParams = null){
        $objApp  = $this->appRepository->findOneByName($appName);
        $objPage = $this->pagesRepository->findByUrl($objApp->app_uuid, $pageURL, $pageUrlParams);

        return ($objPage);
    }

    /**
     * @param array $requestData
     * @return mixed
     */
    public function createPage(array $requestData){
        $objApp  = $this->appRepository->findOneByName($requestData["app_name"]);

        unset($requestData["app_name"]);
        $requestData["app_id"]   = $objApp->app_id;
        $requestData["app_uuid"] = $objApp->app_uuid;

        if ($requestData["page_image"]) {
            $strFileName = md5($requestData["page_image"]->getClientOriginalName() . time()) .
                "." . $requestData["page_image"]->getClientOriginalExtension();
            $strFilePath = "pages";

            Storage::disk("s3-core")->putFileAs(
                $strFilePath,
                $requestData["page_image"],
                $strFileName,
                "public"
            );

            unset($requestData["page_image"]);
            $requestData["page_image"] = $strFileName;
        }

        $objPage = $this->pagesRepository->create($requestData);

        return ($objPage);
    }
}

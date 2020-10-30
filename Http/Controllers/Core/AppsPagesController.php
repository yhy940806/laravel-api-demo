<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Services\Core\AppsPagesService;
use App\Repositories\Core\AppsPagesRepository;
use App\Http\Transformers\Core\AppsPageTransformer;
use App\Http\Requests\{Core\CreatePageRequest, Core\GetPageByUrlRequest};

class AppsPagesController extends Controller {
    /**
     * @var AppsPagesRepository
     */
    private AppsPagesRepository $pagesRepository;
    /**
     * @var AppsPagesService
     */
    private AppsPagesService $pagesService;

    /**
     * PagesController constructor.
     * @param AppsPagesRepository $pagesRepository
     * @param AppsPagesService $pagesService
     */
    public function __construct(AppsPagesRepository $pagesRepository, AppsPagesService $pagesService) {
        $this->pagesService = $pagesService;
        $this->pagesRepository = $pagesRepository;
    }

    /**
     * @group Core
     * @return \Dingo\Api\Http\Response
     */
    public function getPages() {
        return ($this->apiReply($this->pagesRepository->all()));
    }

    /**
     * @group Core
     *
     * @urlParam app_name required App Name
     * @queryParam page_url required Page Url
     *
     * @param GetPageByUrlRequest $request
     * @param string $appName
     * @return \Dingo\Api\Http\Response
     */
    public function getPageByUrl(GetPageByUrlRequest $request, string $appName){
        $objPage = $this->pagesService->getPageByURL($appName, $request->input("page_url"), $request->except("page_url"));

        if (is_null($objPage)) {
            abort (404, "Page not found.");
        }

        return ($this->response->item($objPage, new AppsPageTransformer));
    }

    /**
     * @group Core
     *
     * @urlParam page_uuid required Page UUID
     *
     * @param string $pageUuid
     * @return mixed
     */
    public function getPageByUUID(string $pageUuid) {
        $objPage = $this->pagesRepository->findByUuid($pageUuid);

        return ($this->apiReply($objPage));
    }

    /**
     * @group Core
     *
     * @bodyParam app_name string required App Name
     * @bodyParam page_url string required Page Url
     * @bodyParam page_title string required Page Title
     * @bodyParam page_keywords string required Page Keywords
     * @bodyParam page_url_params[] string required Page Url Parameters
     * @bodyParam page_description string required Page Description
     *
     * @param CreatePageRequest $request
     * @return mixed
     */
    public function addNewPage(CreatePageRequest $request) {
        $objPage = $this->pagesService->createPage($request->only([
            "app_name",
            "page_url",
            "page_title",
            "page_keywords",
            "page_url_params",
            "page_description",
            "page_image"
        ]));

        return ($this->apiReply($objPage));
    }

    /**
     * @group Core
     *
     * @urlParam page_uuid required Page UUID
     *
     * @param string $pageUuid
     * @return mixed
     */
    public function deletePageByUUID(string $pageUuid) {
        $boolResult = $this->pagesRepository->delete($pageUuid);

        if ($boolResult) {
            return ($this->apiReply(null, "Page deleted successfully."));
        }

        return ($this->apiReject(null, "Page haven't deleted."));
    }
}

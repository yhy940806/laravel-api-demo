<?php

namespace App\Repositories\Core;

use App\Models\Core\AppsPage;
use App\Repositories\BaseRepository;

class AppsPagesRepository extends BaseRepository {
    /**
     * ProductRepository constructor.
     * @param AppsPage $page
     */
    public function __construct(AppsPage $page) {
        $this->model = $page;
    }

    /**
     * @param string $appUUID
     * @param string $pageUrl
     * @param array|null $pageUrlParams
     * @return mixed
     */
    public function findByUrl(string $appUUID, string $pageUrl, ?array $pageUrlParams = null){
        $objPage = $this->model->where("app_uuid", $appUUID)->where("page_url", $pageUrl);

        if (is_array($pageUrlParams)) {
            $objPageWithParams = $objPage->where(function ($query) use ($pageUrlParams) {
                foreach ($pageUrlParams as $paramKey => $paramValue) {
                    $query->where("page_url_params->$paramKey", $paramValue);
                }
            })->whereJsonLength("page_url_params", count($pageUrlParams))->first();

            if (isset($objPageWithParams)) {
                return ($objPageWithParams);
            }
        }

        $objPage = $objPage->first();

        return ($objPage);
    }

    /**
     * @param string $pageUuid
     * @return mixed
     */
    public function findByUuid(string $pageUuid) {
        $objPage = $this->model->where("page_uuid", $pageUuid)->first();

        return ($objPage);
    }

    /**
     * @param string $pageUuid
     * @return mixed
     */
    public function delete(string $pageUuid) {
        $boolResult = $this->model->where("page_uuid", $pageUuid)->delete();

        return ($boolResult);
    }
}

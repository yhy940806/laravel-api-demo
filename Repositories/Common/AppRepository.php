<?php

namespace App\Repositories\Common;

use App\Models\Core\App;
use App\Repositories\BaseRepository;
use Illuminate\Support\Collection as SupportCollection;

class AppRepository extends BaseRepository {

    public function __construct(App $objApp) {
        $this->model = $objApp;
    }

    /**
     * @param string $strName
     * @return App
     */
    public function findOneByName(string $strName): App {
        return ($this->model->where("app_name", $strName)->firstOrFail());
    }

    /**
     * @return SupportCollection
     */
    public function findAll(): SupportCollection {
        return ($this->model->all());
    }
}

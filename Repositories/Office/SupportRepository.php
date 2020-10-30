<?php

namespace App\Repositories\Office;

use Util;
use Client;
use App\Models\Core\App;
use App\Models\Support;
use App\Repositories\BaseRepository;

class SupportRepository extends BaseRepository {
    public function __construct(Support $objSupport) {
        $this->model = $objSupport;
    }

    public function findWhere(array $arrWhere) {
        return ($this->model->where(function ($query) use ($arrWhere) {
            foreach ($arrWhere as $key => $value) {
                if (is_string($value)) {
                    $query->whereRaw("lower($key) = (?)", Util::lowerLabel($value));
                } else {
                    $query->where($key, $value);
                }

            }
        })->firstOrFail());
    }

    public function findByCategory(string $category, App $objApp = null) {
        if (!$objApp)
            $objApp = Client::app();
        return ($this->model->whereRaw("lower(support_category) = (?)", Util::lowerLabel($category))
                            ->where("app_id", $objApp->app_id)
                            ->firstOrFail());
    }
}

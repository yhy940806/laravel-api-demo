<?php

namespace App\Repositories\Common;

use Util;
use App\Repositories\BaseRepository;
use App\Models\{User, Soundblock\Service};
use Illuminate\Support\Collection as SupportCollection;

class ServiceRepository extends BaseRepository {
    /**
     * @param Service $service
     * @return void
     */
    public function __construct(Service $service) {
        $this->model = $service;
    }

    /**
     * @param string $term
     * @param string $column
     * @return SupportCollection
     */
    public function findAllLikeName(string $term, string $column = "service_name") {
        $term = Util::lowerLabel($term);
        return ($this->model->whereRaw("lower(" . $column . ") like (?)", ["%{$term}%"])->get());
    }

    public function getActiveUserService(User $objUser): ?Service {
        return $objUser->service()->active()->latest()->first();
    }
}

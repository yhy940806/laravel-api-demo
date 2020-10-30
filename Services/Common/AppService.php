<?php

namespace App\Services\Common;

use App\Models\Core\App;
use Illuminate\Support\Collection;
use App\Repositories\Common\AppRepository;

class AppService {
    /** @var AppRepository */
    protected AppRepository $appRepo;

    /**
     * @param AppRepository $appRepo
     */
    public function __construct(AppRepository $appRepo) {
        $this->appRepo = $appRepo;
    }

    /**
     * @param mixed $id
     * @param bool $bnFailure
     */
    public function find($id, ?bool $bnFailure = true): ?App {
        return ($this->appRepo->find($id, $bnFailure));
    }

    /**
     * @param string $strAppName
     */
    public function findOneByName(string $strAppName): App {
        return ($this->appRepo->findOneByName($strAppName));
    }

    /**
     * @return Collection
     */
    public function findAll(): Collection {
        return ($this->appRepo->findAll());
    }
}

<?php

namespace App\Repositories\Soundblock;

use Util;
use App\Repositories\BaseRepository;
use App\Models\Soundblock\DeploymentStatus;

class DeploymentStatusRepository extends BaseRepository {
    public function __construct(DeploymentStatus $objStatus) {
        $this->model = $objStatus;
    }

    public function createModel(array $arrParams) {
        $objStatus = new DeploymentStatus;

        $objStatus->row_uuid = Util::uuid();
        $objStatus->deployment_id = $arrParams["deployment_id"];
        $objStatus->deployment_uuid = $arrParams["deployment_uuid"];
        $objStatus->deployment_status = $arrParams["deployment_status"];
        $objStatus->deployment_memo = $arrParams["deployment_memo"];

        $objStatus->save();

        return ($objStatus);
    }
}

<?php

namespace App\Repositories\Core;

use App\Models\Core\Correspondence;
use App\Repositories\BaseRepository;

class CorrespondenceRepository extends BaseRepository {
    /**
     * @var Correspondence
     */
    private $Correspondence;

    /**
     * CorrespondenceRepository constructor.
     * @param Correspondence $correspondence
     */
    public function __construct(Correspondence $correspondence){
        $this->model = $correspondence;
    }

    /**
     * @param array $insertData
     * @return mixed
     */
    public function create(array $insertData){
        $objCorrespondence = $this->model->create($insertData);

        return ($objCorrespondence);
    }

    /**
     * @param string $correspondenceUUID
     * @return mixed
     */
    public function findByUUID(string $correspondenceUUID){
        $objCorrespondence = $this->model->where("correspondence_uuid", $correspondenceUUID)->first();

        return ($objCorrespondence);
    }

    /**
     * @param string $correspondenceUUID
     * @param array $updateData
     * @return mixed
     */
    public function updateByUUID(string $correspondenceUUID, array $updateData){
        $objCorrespondence = $this->model->where("correspondence_uuid", $correspondenceUUID)->update($updateData);

        return ($objCorrespondence);
    }
}

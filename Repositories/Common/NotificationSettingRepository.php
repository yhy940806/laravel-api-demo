<?php

namespace App\Repositories\Common;

use App\Models\NotificationSetting;
use App\Repositories\BaseRepository;

class NotificationSettingRepository extends BaseRepository {
    /**
     * @param NotificationSetting $objSetting
     */
    public function __construct(NotificationSetting $objSetting) {
        $this->model = $objSetting;
    }

    public function create(array $arrParams) {
        $this->model = $this->model->newInstance();
        return parent::create($arrParams);
    }
}

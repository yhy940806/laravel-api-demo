<?php

namespace App\Repositories\Apparel;

use App\Models\Apparel\File;
use App\Repositories\BaseRepository;

class FileRepository extends BaseRepository {
    /**
     * @param File $file
     *
     * @return void
     */
    public function __construct(File $file) {
        $this->model = $file;
    }
}

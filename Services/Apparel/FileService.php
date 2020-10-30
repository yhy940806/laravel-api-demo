<?php

namespace App\Services\Apparel;

use App\Repositories\Apparel\FileRepository;

class FileService {
    /**
     * @var FileRepository
     */
    private FileRepository $fileRepository;

    /**
     * @param FileRepository $fileRepository
     *
     * @return void
     */
    public function __construct(FileRepository $fileRepository) {
        $this->fileRepository = $fileRepository;
    }

    /**
     * @param mixed $id
     * @param bool $bnFailure
     *
     * @return \App\Models\Apparel\File
     */
    public function find($id, bool $bnFailure = true) {
        return ($this->fileRepository->find($id, $bnFailure));
    }
}

<?php

namespace App\Repositories\Soundblock;

use App\Repositories\BaseRepository;
use App\Models\Soundblock\{FileHistory, File};

class FileHistoryRepository extends BaseRepository {
    protected File $fileModel;

    public function __construct(FileHistory $objHistory, File $objFile) {
        $this->model = $objHistory;
        $this->fileModel = $objFile;
    }

    /**
     * @param File $objParent
     * @return File
     */
    public function findChild(File $objParent): File {
        $child = $this->model->where("file_id", $objParent->file_id)->orderBy("collection_id", "asc")->firstOrFail();
        while ($child) {
            $child = $this->model->where("parent_id", $child->file_id)->orderBy("collection_id", "asc")->first();
            if ($child && $child->file)
                $objChildFile = $child->file;
        }

        return ($objChildFile);
    }

    /**
     * @param File $objFile
     *
     * @return FileHistory
     */
    public function getLatestHistoryByFile(File $objFile) {
        return ($this->model->where("file_id", $objFile->file_id)
                            ->orderBy("collection_id", "desc")->first());
    }
}

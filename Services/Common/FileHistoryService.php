<?php

namespace App\Services\Common;

use Util;
use Exception;
use App\Models\Soundblock\{FileHistory, File};

class FileHistoryService {

    public function findChild($parent) {
        if (is_int($parent)) {
            $objFile = File::findOrFail($parent);
        } else if (is_string($parent) && Util::is_uuid($parent)) {
            $objFile = File::where("file_uuid", $parent)->firstOrFail();
        } else if ($parent instanceof File) {
            $objFile = $parent;
        } else {
            throw new Exception("File id or uuid is invalid", 417);
        }

        $child = FileHistory::where("parent_id", $objFile->file_id)->orderBy("collection_id", "desc")->first();

        $objChild = File::findOrFail($child->file_id);
        return ($objChild);
    }

    public function create($arrHistory) {
        $objFileHistory = new FileHistory();
        $objFileHistory->row_uuid = Util::uuid();

        return ($this->update($objFileHistory, $arrHistory));
    }

    public function update(FileHistory $objFileHistory, $arrHistory) {

        if (isset($arrHistory["parent"])) {
            $objParentHistory = $this->find($arrHistory["parent"]);
            $objFileHistory->parent_id = $objParentHistory->row_id;
            $objFileHistory->parent_uuid = $objParentHistory->row_uuid;
        }

        $objFile = File::where("file_uuid", $arrHistory["file"])->firstOrFail();
        $objFileHistory->file_id = $objFile->file_id;
        $objFileHistory->file_uuid = $objFile->file_uuid;
        $objFileHistory->file_action = Util::ucfLabel($arrHistory["file_action"]);
        $objFileHistory->file_category = $objFile->file_category;
        $objFileHistory->file_memo = $arrHistory["file_memo"];

        $objFileHistory->save();

        return ($objFileHistory);
    }

    public function find($row) {
        if (is_int($row)) {
            return (FileHistory::findOrFail($row));
        } else if (is_string($row)) {
            return (FileHistory::where("row_uuid", $row)->firstOrFail());
        } else {
            throw new Exception();
        }
    }

}

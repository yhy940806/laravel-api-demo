<?php

namespace App\Observers\Soundblock;

use Util;
use Auth;
use App\Models\BaseModel;
use App\Models\Soundblock\File;

class FileObserver {
    public function modified(File $objFile) {
        $objFile->{BaseModel::STAMP_MODIFIED} = time();
        $objFile->{BaseModel::MODIFIED_AT} = Util::now();
        $objFile->{BaseModel::STAMP_MODIFIED_BY} = Auth::id();

        $objFile->save();
    }
}

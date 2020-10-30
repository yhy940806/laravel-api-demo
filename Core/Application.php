<?php

namespace App\Core;

class Application extends \Illuminate\Foundation\Application {
    public function storagePath() {
        return (sprintf("%s/storage", $this->basePath));
    }
}

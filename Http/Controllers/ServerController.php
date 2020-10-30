<?php

namespace App\Http\Controllers;

use App\Models\Core\App;

class ServerController extends Controller {

    public function ping() {
        return ($this->apiReply());
    }

    public function get() {
        return ($this->apiReply(App::all()));
    }

    public function version() {
        if (file_exists(base_path("version"))) {
            return ($this->apiReply(file_get_contents(base_path("version"))));
        }

        return ($this->apiReply("develop", "", 400));
    }
}

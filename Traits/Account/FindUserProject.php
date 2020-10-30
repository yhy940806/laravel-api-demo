<?php

namespace App\Traits\Account;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait FindUserProject {
    public function getUserProject($strProjectUuid) {
        /** @var User $objUser */
        $objUser = Auth::user();

        $objProject = $this->projectService->findUserProject($strProjectUuid, $objUser);

        if (!$objProject) {
            abort(404);
        }

        return ($objProject);
    }
}

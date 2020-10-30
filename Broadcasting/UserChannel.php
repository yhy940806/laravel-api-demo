<?php

namespace App\Broadcasting;

use App\Models\User;
use Log;

class UserChannel {
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param User $objUser
     * @param string $user
     * @return array|bool
     */
    public function join(User $objUser, string $user) {
        Log::info("User->Notification", ["user" => $user, "auth-user" => $objUser->user_uuid]);

        return ($objUser->user_uuid == $user);
    }
}

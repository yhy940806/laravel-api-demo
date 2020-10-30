<?php

namespace App\Events\Common;

use App\Models\UserContactEmail;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var UserContactEmail
     */
    protected UserContactEmail $email;

    /**
     * Create a new event instance.
     * @param UserContactEmail $email
     */
    public function __construct(UserContactEmail $email) {
        $this->email = $email;
    }
}

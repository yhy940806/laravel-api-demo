<?php

namespace App\Listeners\Common;

use App\Services\EmailService;

class VerifyEmailListener {
    protected EmailService $emailService;

    /**
     * Create the event listener.
     *
     * @param EmailService $emailService
     */
    public function __construct(EmailService $emailService) {
        //
        $this->emailService = $emailService;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event) {
        /** @var \App\Models\UserContactEmail */
        $objEmail = $event->objEmail;

        $this->emailService->sendVerificationEmail($objEmail);
    }
}

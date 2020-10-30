<?php

namespace App\Listeners\User;

use App\Models\User;
use App\Services\EmailService;

class DeleteEmailListener {
    /** @var EmailService */
    protected EmailService $emailService;

    /**
     * Create the event listener.
     * @param EmailService $emailService
     * @return void
     */
    public function __construct(EmailService $emailService) {
        $this->emailService = $emailService;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     * @throws \Exception
     */
    public function handle($event) {
        /** @var User $user */
        $user = $event->user;
        $hasPrimary = $this->emailService->hasPrimary($user);
        if (!$hasPrimary) {
            $verifiedEmails = $this->emailService->verifiedEmails($user);
            if ($verifiedEmails->isNotEmpty()) {
                $this->emailService->update($verifiedEmails->first(), $user, ["flag_primary" => true]);
            } else {
                $this->emailService->update($user->emails->first(), $user, ["flag_primary" => true]);
            }
        }
    }
}

<?php

namespace App\Listeners\Common;

use Util;
use App\Models\{Core\App, UserContactEmail};
use Illuminate\Mail\Events\MessageSent;
use App\Services\{UserService, EmailService, User\UserCorrespondenceService};

class MessageSentListener {
    /**
     * @var UserService
     */
    private UserService $userService;
    /**
     * @var UserCorrespondenceService
     */
    private UserCorrespondenceService $correspondenceService;
    /**
     * @var EmailService
     */
    private EmailService $emailService;

    /**
     * Create the event listener.
     * @param UserService $userService
     * @param UserCorrespondenceService $correspondenceService
     * @param EmailService $emailService
     *
     * @return void
     */
    public function __construct(UserService $userService, UserCorrespondenceService $correspondenceService, EmailService $emailService) {
        $this->userService = $userService;
        $this->correspondenceService = $correspondenceService;
        $this->emailService = $emailService;
    }

    /**
     * Handle the event.
     *
     * @param MessageSent $event
     * @return void
     * @throws \Exception
     */
    public function handle(MessageSent $event) {
        /**
         * @var array $recpients
         */
        $arrEmails = array_keys($event->message->getTo());
        /**
         * @var App
         */
        $app = $event->message->app;
        $from = array_keys($event->message->getFrom());
        $uuid = Util::uuid();

        foreach ($arrEmails as $strEmail) {
            /** @var UserContactEmail */
            $email = $this->emailService->find($strEmail);
            if (!$email)
                continue;
            $user = $email->user;
            $params = [
                "email_id"      => $event->message->getId(),
                "email_uuid"    => $uuid,
                "email_subject" => $event->message->getSubject(),
                "email_from"    => $from[0],
                "email_text"    => "Sample Text",
                "email_html"    => $event->message->getBody(),
            ];
            $this->correspondenceService->create($params, $user, $app);
        }
    }
}

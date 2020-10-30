<?php

namespace App\Listeners\User;

use App\Services\User\UserNoteAttachmentService;

class UserNoteAttachListener {

    protected UserNoteAttachmentService $attachService;

    /**
     * Create the event listener.
     *
     * @param UserNoteAttachmentService $attachService
     */
    public function __construct(UserNoteAttachmentService $attachService) {
        $this->attachService = $attachService;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event) {
        $objNote = $event->objNote;
        $arrUrls = $event->arrUrls;

        foreach ($arrUrls as $url) {
            $arrParams = [];

            $arrParams["note"] = $objNote;
            $arrParams["attachment_url"] = $url;
            $this->attachService->create($arrParams);
        }
    }
}

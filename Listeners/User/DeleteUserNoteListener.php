<?php

namespace App\Listeners\User;

use App\Services\User\UserNoteAttachmentService;

class DeleteUserNoteListener {
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

        $this->attachService->delete($objNote);
    }
}

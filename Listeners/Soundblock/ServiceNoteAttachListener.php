<?php

namespace App\Listeners\Soundblock;

use App\Services\Soundblock\ServiceNoteAttachService;

class ServiceNoteAttachListener {
    protected ServiceNoteAttachService $attachService;

    /**
     * Create the event listener.
     *
     * @param ServiceNoteAttachService $attachService
     */
    public function __construct(ServiceNoteAttachService $attachService) {
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
        $urls = $event->urls;

        foreach ($urls as $url) {
            $arrParams = [];
            $arrParams["attachment_url"] = $url;

            $this->attachService->create($objNote, $arrParams);
        }

    }
}

<?php

namespace App\Listeners\Office\Support;

use App\Services\Office\SupportTicketAttachmentService;

class TicketAttachListener {
    protected SupportTicketAttachmentService $attachService;

    /**
     * Create the event listener.
     *
     * @param SupportTicketAttachmentService $attachService
     */
    public function __construct(SupportTicketAttachmentService $attachService) {
        $this->attachService = $attachService;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event) {
        $objMsg = $event->objMsg;
        $arrMeta = $event->arrMeta;

        foreach ($arrMeta as $meta) {
            $arrParams = [];
            $arrParams["attachment_name"] = $meta["attachment_name"];
            $arrParams["attachment_url"] = $meta["attachment_url"];

            $this->attachService->create($objMsg, $arrParams);
        }
    }
}

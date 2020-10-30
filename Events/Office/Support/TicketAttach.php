<?php

namespace App\Events\Office\Support;

use App\Models\SupportTicketMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class TicketAttach {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public SupportTicketMessage $objMsg;

    public array $arrMeta;

    /**
     * Create a new event instance.
     *
     * @param SupportTicketMessage $objMsg
     * @param array $arrMeta
     */
    public function __construct(SupportTicketMessage $objMsg, array $arrMeta) {
        $this->objMsg = $objMsg;
        $this->arrMeta = $arrMeta;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {
        return new PrivateChannel('channel-name');
    }
}

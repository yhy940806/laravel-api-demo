<?php

namespace App\Events\Office\Support;

use Util;
use App\Models\Core\Auth\AuthModel;
use App\Models\SupportTicket;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TicketNotification implements ShouldBroadcast {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public SupportTicket $objTicket;

    public AuthModel $objAuth;

    public array $arrMsg;

    /**
     * Create a new event instance.
     *
     * @param array $arrContents
     * @param SupportTicket $objTicket
     * @param AuthModel $objAuth
     */
    public function __construct(array $arrContents, SupportTicket $objTicket, AuthModel $objAuth) {
        $this->arrMsg = $arrContents;
        $this->objTicket = $objTicket;
        $this->objAuth = $objAuth;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {
        return (new PrivateChannel("channel." . Util::lowerLabel($this->objAuth->auth_name) . "support.ticket." . $this->objTicket->ticket_uuid));
    }

    public function broadcastAs() {
        return ("Notify.Support.Ticket." . $this->objTicket->ticket_uuid);
    }

    public function broadcastWith() {
        return ($this->arrMsg);
    }
}

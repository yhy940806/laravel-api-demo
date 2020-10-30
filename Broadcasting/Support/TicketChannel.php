<?php

namespace App\Broadcasting\Support;

use App\Models\User;
use App\Services\Office\SupportTicketService;

class TicketChannel {

    protected SupportTicketService $ticketService;

    /**
     * Create a new channel instance.
     *
     * @param SupportTicketService $ticketService
     */
    public function __construct(SupportTicketService $ticketService) {
        $this->ticketService = $ticketService;
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param User $user
     * @param string $ticket
     * @return array|bool
     */
    public function join(User $user, string $ticket) {
        $objTicket = $this->ticketService->find($ticket);

        return ($objTicket->user->user_id == $user->user_id);
    }
}

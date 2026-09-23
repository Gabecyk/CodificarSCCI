<?php

namespace App\Actions\Tickets;

use App\Repositories\Contracts\TicketRepositoryInterface;
use App\Models\Ticket;

class ShowTicketAction
{
    public function __construct(
        private readonly TicketRepositoryInterface $tickets,
    ) {}

    /**
     * @param  int  $id
     */
    public function execute(int $id): Ticket
    {
        return $this->tickets->findOrFail($id);
    }
}

<?php

namespace App\Actions\Tickets;

use App\Repositories\Contracts\TicketRepositoryInterface;
use App\Services\TicketAssignment\Contracts\AssignmentStrategyInterface;
use App\Models\Ticket;

class UpdateTicketAction
{
    public function __construct(
        private readonly TicketRepositoryInterface $tickets,
        private readonly AssignmentStrategyInterface $assignmentStrategy,
    ) {}

    public function execute(array $data, int $id): Ticket
    {
        $ticket = $this->tickets->findOrFail($id);
        $ticket->updated_at = now();
        $ticket->update($data);

        return $this->assignmentStrategy->assign($ticket);
    }
}

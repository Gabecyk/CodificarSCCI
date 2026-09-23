<?php

namespace App\Services\TicketAssignment\Strategies;

use App\Models\Responsible;
use App\Models\Ticket;
use App\Services\TicketAssignment\Contracts\AssignmentStrategyInterface;

class LeastBusyAgentStrategy implements AssignmentStrategyInterface
{
    public function assign(Ticket $ticket): Ticket
    {
        if($ticket->responsible_id)
            return $ticket;

        $leastBusyAgent = Responsible::withCount('openTickets')
            ->orderBy('open_tickets_count')
            ->orderBy('id')
            ->first();

        if ($leastBusyAgent) {
            $ticket->responsible_id = $leastBusyAgent->id;
            $ticket->save();
        }

        return $ticket;
    }
}
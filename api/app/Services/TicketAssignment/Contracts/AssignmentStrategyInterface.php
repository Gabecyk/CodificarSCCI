<?php

namespace App\Services\TicketAssignment\Contracts;

use App\Models\Ticket;

interface AssignmentStrategyInterface
{
    public function assign(Ticket $ticket): Ticket;
}

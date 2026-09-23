<?php 

namespace App\Actions\Tickets;

use App\Repositories\Contracts\TicketRepositoryInterface;
use App\Services\TicketAssignment\Contracts\AssignmentStrategyInterface;

class CreateTicketAction
{
    public function __construct(
        private readonly TicketRepositoryInterface $tickets,
        private readonly AssignmentStrategyInterface $assignmentStrategy,
    ) {}

    public function execute(array $data)
    {
        $ticket = $this->tickets->store($data);
        
        return $this->assignmentStrategy->assign($ticket);
    }
}
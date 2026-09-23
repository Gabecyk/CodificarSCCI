<?php

namespace App\Actions\Tickets;

use App\Repositories\Contracts\TicketRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListTicketsAction
{
    public function __construct(
        private readonly TicketRepositoryInterface $tickets,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->tickets->paginate($filters, $perPage);
    }
}

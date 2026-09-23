<?php

namespace App\Repositories\Eloquent;

use App\Models\Ticket;
use App\Repositories\Contracts\TicketRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TicketRepository implements TicketRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Ticket::query()
            ->with('responsible')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['priority'] ?? null, fn ($query, $priority) => $query->where('priority', $priority))
            ->when($filters['responsible_id'] ?? null, fn ($query, $responsibleId) => $query->where('responsible_id', $responsibleId))
            ->latest('opened_at')
            ->paginate($perPage);
    }

    public function store(array $data): Ticket
    {
        $ticket = Ticket::create($data);

        return $ticket->refresh();
    }

    public function findOrFail(int $id): Ticket
    {
        return Ticket::findOrFail($id);
    }
}

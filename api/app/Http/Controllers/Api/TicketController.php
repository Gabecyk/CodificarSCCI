<?php

namespace App\Http\Controllers\Api;

use App\Actions\Tickets\ListTicketsAction;
use App\Actions\Tickets\CreateTicketAction;
use App\Actions\Tickets\ShowTicketAction;
use App\Actions\Tickets\UpdateTicketAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Http\Requesters\StoreTicketRequest;
use App\Http\Requesters\UpdateTicketRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketController extends Controller
{
    public function index(Request $request, ListTicketsAction $action): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'priority', 'responsible_id']);

        $tickets = $action->execute($filters, (int) $request->integer('per_page', 15));

        return TicketResource::collection($tickets);
    }

    public function store(StoreTicketRequest $request, CreateTicketAction $action): TicketResource
    {
        $ticket = $action->execute($request->validated());

        return new TicketResource($ticket);
    }

    public function show(int $id, ShowTicketAction $action): TicketResource
    {
        $ticket = $action->execute($id);

        return new TicketResource($ticket);
    }

    public function update(int $id, UpdateTicketRequest $request, UpdateTicketAction $action): TicketResource
    {
        $ticket = $action->execute($request->validated(), $id);

        return new TicketResource($ticket);
    }
}

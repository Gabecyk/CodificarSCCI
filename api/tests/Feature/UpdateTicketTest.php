<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\Responsible;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTicketTest extends TestCase
{
    use RefreshDatabase;

    // Testa a funcionalidade de atualizar um Ticket
    public function test_update_ticket(): void
    {
        $responsibleA = Responsible::factory()->create();
        $ticketA = Ticket::factory()->create([
            'responsible_id' => $responsibleA->id,
        ]);

        $ticketA->update([
            'status' => TicketStatus::RESOLVED->value,
        ]);

        $this->assertSame(TicketStatus::RESOLVED, $ticketA->fresh()->status);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticketA->id,
            'status' => TicketStatus::RESOLVED->value,
        ]);
    }
}
<?php

namespace Tests\Feature;

use App\Models\Responsible;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTicketTest extends TestCase
{
    use RefreshDatabase;

    //Testa a funcionalidade de criar um Ticket
    public function test_store_the_ticket(): void
    {
        $responsibleA = Responsible::factory()->create();
        $ticketA = Ticket::factory()->create([
            'responsible_id' => $responsibleA->id,
        ]);

        $this->assertSame($responsibleA->id, $ticketA->fresh()->responsible_id);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticketA->id,
            'responsible_id' => $responsibleA->id,
        ]);
    }
}
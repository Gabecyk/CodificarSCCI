<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\Responsible;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTicketTest extends TestCase
{
    use RefreshDatabase;

    // Testa o get de tickets, verificando se o ticket criado está presente na resposta.
    public function test_index_tickets(): void
    {
        $responsibleA = Responsible::factory()->create();
        $ticketA = Ticket::factory()->create([
            'responsible_id' => $responsibleA->id,
        ]);

        $response = $this->getJson('/api/tickets');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $ticketA->id,
                'title' => $ticketA->title,
                'description' => $ticketA->description,
                'employee_email' => $ticketA->employee_email,
                'priority' => $ticketA->priority,
                'status' => $ticketA->status,
                'responsible_id' => $ticketA->responsible_id,
            ]);
    }
}
<?php

namespace Tests\Feature;

use App\Models\Responsible;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTicketTest extends TestCase
{
    use RefreshDatabase;

    // Testa GET /api/tickets/{id}: retorna 200 com os dados do chamado.
    public function test_show_returns_the_ticket(): void
    {
        $responsible = Responsible::factory()->create();
        $ticket = Ticket::factory()->create([
            'title' => 'Computador travou',
            'description' => 'Trava ao abrir o navegador.',
            'employee_email' => 'maria@empresa.com',
            'priority' => 'high',
            'status' => 'in_progress',
            'responsible_id' => $responsible->id,
        ]);

        $this->getJson("/api/tickets/{$ticket->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $ticket->id)
            ->assertJsonPath('data.title', 'Computador travou')
            ->assertJsonPath('data.description', 'Trava ao abrir o navegador.')
            ->assertJsonPath('data.employee_email', 'maria@empresa.com')
            ->assertJsonPath('data.priority', 'high')
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.responsible_id', $responsible->id);
    }

    // Testa que consultar um chamado inexistente retorna 404.
    public function test_show_returns_404_for_an_unknown_ticket(): void
    {
        $this->getJson('/api/tickets/9999')
            ->assertStatus(404);
    }
}

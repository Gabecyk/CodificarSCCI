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

    // Testa que os chamados mais recentemente abertos vêm primeiro.
    public function test_index_orders_by_most_recently_opened_first(): void
    {
        $oldest = Ticket::factory()->create(['opened_at' => now()->subDays(3)]);
        $newest = Ticket::factory()->create(['opened_at' => now()->subDay()]);
        $middle = Ticket::factory()->create(['opened_at' => now()->subDays(2)]);

        $this->getJson('/api/tickets')
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', $newest->id)
            ->assertJsonPath('data.1.id', $middle->id)
            ->assertJsonPath('data.2.id', $oldest->id);
    }

    // Testa o filtro por status.
    public function test_index_filters_by_status(): void
    {
        Ticket::factory()->count(2)->create(['status' => TicketStatus::OPEN->value]);
        $resolved = Ticket::factory()->create(['status' => TicketStatus::RESOLVED->value]);

        $this->getJson('/api/tickets?status=resolved')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $resolved->id)
            ->assertJsonPath('data.0.status', 'resolved');
    }

    // Testa a paginação: per_page limita os itens e page navega entre as páginas.
    public function test_index_paginates_with_per_page(): void
    {
        Ticket::factory()->count(5)->create();

        $this->getJson('/api/tickets?per_page=2&page=1')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('meta.last_page', 3);

        $this->getJson('/api/tickets?per_page=2&page=3')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.current_page', 3);
    }
}

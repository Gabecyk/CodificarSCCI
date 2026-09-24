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

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Título atualizado',
            'description' => 'Descrição atualizada.',
            'priority' => 'high',
            'employee_email' => 'novo@empresa.com',
        ], $overrides);
    }

    // Testa PUT /api/tickets/{id}: atualiza os campos (inclusive o status) e retorna 200.
    public function test_put_updates_the_ticket_and_returns_200(): void
    {
        $responsible = Responsible::factory()->create();
        $ticket = Ticket::factory()->create([
            'responsible_id' => $responsible->id,
            'status' => TicketStatus::OPEN->value,
        ]);

        $response = $this->putJson("/api/tickets/{$ticket->id}", $this->validPayload([
            'responsible_id' => $responsible->id,
            'status' => 'resolved',
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $ticket->id)
            ->assertJsonPath('data.title', 'Título atualizado')
            ->assertJsonPath('data.status', 'resolved');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'title' => 'Título atualizado',
            'description' => 'Descrição atualizada.',
            'priority' => 'high',
            'employee_email' => 'novo@empresa.com',
            'status' => 'resolved',
        ]);
    }

    // Testa que a validação falha (422) sem alterar o chamado quando faltam campos obrigatórios.
    public function test_put_fails_validation_and_keeps_the_ticket_unchanged(): void
    {
        $ticket = Ticket::factory()->create(['title' => 'Título original']);

        $payload = $this->validPayload();
        unset($payload['title']);

        $this->putJson("/api/tickets/{$ticket->id}", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('title');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'title' => 'Título original',
        ]);
    }

    // Testa que atualizar um chamado inexistente retorna 404.
    public function test_put_returns_404_for_an_unknown_ticket(): void
    {
        $this->putJson('/api/tickets/9999', $this->validPayload())
            ->assertStatus(404);
    }

    // Testa que enviar responsible_id nulo redistribui automaticamente para quem tem menos chamados em aberto.
    public function test_put_with_null_responsible_auto_assigns_to_the_least_busy(): void
    {
        $busy = Responsible::factory()->create();
        $free = Responsible::factory()->create();

        $ticket = Ticket::factory()->create([
            'responsible_id' => $busy->id,
            'status' => TicketStatus::OPEN->value,
        ]);
        Ticket::factory()->create([
            'responsible_id' => $busy->id,
            'status' => TicketStatus::OPEN->value,
        ]);

        $this->putJson("/api/tickets/{$ticket->id}", $this->validPayload([
            'responsible_id' => null,
        ]))
            ->assertStatus(200)
            ->assertJsonPath('data.responsible_id', $free->id);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'responsible_id' => $free->id,
        ]);
    }
}

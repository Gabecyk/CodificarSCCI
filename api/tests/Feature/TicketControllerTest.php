<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\Responsible;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketControllerTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Impressora do financeiro não funciona',
            'description' => 'A impressora não imprime desde ontem.',
            'priority' => 'medium',
            'employee_email' => 'funcionario@empresa.com',
        ], $overrides);
    }

    // Testa a criação de um ticket com sucesso e verifica se o status retornado é 201.
    public function test_store_creates_a_ticket_and_returns_201(): void
    {
        $responsible = Responsible::factory()->create();

        $payload = [
            'title' => 'Erro ao acessar o sistema de pagamento',
            'description' => 'Ao tentar finalizar a compra, a tela fica carregando infinitamente.',
            'priority' => 'high',
            'employee_email' => 'funcionario@empresa.com',
            'responsible_id' => $responsible->id,
        ];

        $response = $this->postJson('/api/tickets', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('tickets', [
            'title' => $payload['title'],
            'description' => $payload['description'],
            'priority' => $payload['priority'],
            'employee_email' => $payload['employee_email'],
            'responsible_id' => $responsible->id,
            'status' => 'open',
        ]);
    }

    // Testa a criação do ticket sem informar o titulo, esperando que a validação falhe e retorne um erro 422.
    public function test_store_fails_validation_without_title(): void
    {
        $payload = [
            'description' => 'Descrição do chamado.',
            'priority' => 'high',
            'employee_email' => 'funcionario@empresa.com',
        ];

        $response = $this->postJson('/api/tickets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');

        $this->assertDatabaseCount('tickets', 0);
    }

    // Testa a criação do ticket com prioridade inválida, esperando que a validação falhe e retorne um erro 422.
    public function test_store_fails_validation_with_invalid_priority(): void
    {
        $payload = [
            'title' => 'Erro ao acessar o sistema de pagamento',
            'description' => 'Descrição do chamado.',
            'priority' => 'muito urgente', // Prioridade inválida
            'employee_email' => 'funcionario@empresa.com',
        ];

        $response = $this->postJson('/api/tickets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('priority');

        $this->assertDatabaseCount('tickets', 0);
    }

    // Testa a atribuição automática via API: sem responsible_id, vai para quem tem menos chamados em aberto.
    public function test_store_auto_assigns_to_the_responsible_with_fewest_open_tickets(): void
    {
        $busy = Responsible::factory()->create();
        $free = Responsible::factory()->create();

        Ticket::factory()->count(2)->create([
            'responsible_id' => $busy->id,
            'status' => TicketStatus::OPEN->value,
        ]);

        $response = $this->postJson('/api/tickets', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonPath('data.responsible_id', $free->id);

        $this->assertDatabaseHas('tickets', [
            'title' => 'Impressora do financeiro não funciona',
            'responsible_id' => $free->id,
        ]);
    }

    // Testa que o chamado em andamento conta como carga na atribuição automática (definição de "em aberto").
    public function test_store_auto_assignment_counts_in_progress_tickets_as_open(): void
    {
        $inProgress = Responsible::factory()->create();
        $idle = Responsible::factory()->create();

        Ticket::factory()->create([
            'responsible_id' => $inProgress->id,
            'status' => TicketStatus::IN_PROGRESS->value,
        ]);

        $response = $this->postJson('/api/tickets', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonPath('data.responsible_id', $idle->id);
    }

    // Testa que a escolha manual é respeitada, mesmo que o responsável escolhido seja o mais ocupado.
    public function test_store_respects_the_manually_chosen_responsible(): void
    {
        Responsible::factory()->create();
        $busy = Responsible::factory()->create();

        Ticket::factory()->count(3)->create([
            'responsible_id' => $busy->id,
            'status' => TicketStatus::OPEN->value,
        ]);

        $response = $this->postJson('/api/tickets', $this->validPayload([
            'responsible_id' => $busy->id,
        ]));

        $response->assertStatus(201)
            ->assertJsonPath('data.responsible_id', $busy->id);
    }

    // Testa que o status enviado pelo cliente é ignorado: todo chamado nasce "open".
    public function test_store_ignores_the_status_sent_by_the_client(): void
    {
        $response = $this->postJson('/api/tickets', $this->validPayload([
            'status' => 'closed',
        ]));

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'open');

        $this->assertDatabaseHas('tickets', [
            'title' => 'Impressora do financeiro não funciona',
            'status' => 'open',
        ]);
    }
}

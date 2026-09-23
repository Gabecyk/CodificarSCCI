<?php

namespace Tests\Feature;

use App\Models\Responsible;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketControllerTest extends TestCase
{
    use RefreshDatabase;

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
}

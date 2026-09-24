<?php

namespace Tests\Feature;

use App\Models\Responsible;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexResponsibleTest extends TestCase
{
    use RefreshDatabase;

    // Testa GET /api/responsibles: lista os responsáveis disponíveis para atribuição.
    public function test_index_lists_the_responsibles(): void
    {
        $responsibles = Responsible::factory()->count(3)->create();

        $response = $this->getJson('/api/responsibles');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        foreach ($responsibles as $responsible) {
            $response->assertJsonFragment([
                'id' => $responsible->id,
                'name' => $responsible->name,
                'email' => $responsible->email,
            ]);
        }
    }
}

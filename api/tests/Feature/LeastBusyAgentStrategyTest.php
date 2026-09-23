<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\Responsible;
use App\Models\Ticket;
use App\Services\TicketAssignment\Strategies\LeastBusyAgentStrategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeastBusyAgentStrategyTest extends TestCase
{
    use RefreshDatabase;

    //Testa a funcionalidade de atribuição de tickets para o responsável com menos tickets abertos.
    public function test_assigns_the_ticket_to_the_responsible_with_fewest_open_tickets(): void
    {
        $responsibleA = Responsible::factory()->create();
        $responsibleB = Responsible::factory()->create();
        $responsibleC = Responsible::factory()->create();

        Ticket::factory()->count(2)->create([
            'responsible_id' => $responsibleA->id,
            'status' => TicketStatus::OPEN->value,
        ]);

        Ticket::factory()->create([
            'responsible_id' => $responsibleC->id,
            'status' => TicketStatus::OPEN->value,
        ]);

        $newTicket = Ticket::factory()->create([
            'responsible_id' => null,
        ]);

        $assigned = (new LeastBusyAgentStrategy())->assign($newTicket);

        $this->assertSame($responsibleB->id, $assigned->responsible_id);
        $this->assertSame($responsibleB->id, $newTicket->fresh()->responsible_id);
    }

    // Testa se tickets com status "resolvido" e "fechado" não são contabilizados como carga de trabalho aberta.
    public function test_resolved_and_closed_tickets_do_not_count_as_open_load(): void
    {
        $overloadedButResolved = Responsible::factory()->create();
        $slightlyBusy = Responsible::factory()->create();

        Ticket::factory()->count(5)->create([
            'responsible_id' => $overloadedButResolved->id,
            'status' => TicketStatus::RESOLVED->value,
        ]);

        Ticket::factory()->count(3)->create([
            'responsible_id' => $overloadedButResolved->id,
            'status' => TicketStatus::CLOSED->value,
        ]);

        Ticket::factory()->create([
            'responsible_id' => $slightlyBusy->id,
            'status' => TicketStatus::OPEN->value,
        ]);

        $newTicket = Ticket::factory()->create([
            'responsible_id' => null,
        ]);

        $assigned = (new LeastBusyAgentStrategy())->assign($newTicket);

        // $overloadedButResolved tem 8 tickets no total, mas 0 em aberto —
        // deve ganhar de $slightlyBusy, que tem só 1 ticket, mas em aberto.
        $this->assertSame($overloadedButResolved->id, $assigned->responsible_id);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Responsible;
use App\Models\Ticket;
use App\Services\TicketAssignment\Strategies\LeastBusyAgentStrategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponsibleActionTest extends TestCase
{
    use RefreshDatabase;

    //Testa a funcionalidade de atribuição de tickets para o responsável com menos tickets abertos ao enviar um ticket com responsável null.
    public function test_assigns_the_ticket_to_the_responsible_null_in_ticket_payload(): void
    {
        $responsibleA = Responsible::factory()->create();
        $ticketA = Ticket::factory()->create([
            'responsible_id' => null,
        ]);

        $assigned = (new LeastBusyAgentStrategy())->assign($ticketA);

        $this->assertSame($responsibleA->id, $assigned->responsible_id);
        $this->assertSame($responsibleA->id, $ticketA->fresh()->responsible_id);
    }

    // Testa a funcionalidade de atribuição de tickets para o responsável com menos tickets abertos ao enviar um ticket com responsável já definido.
    public function test_assigns_the_ticket_to_the_responsible_sended_in_payload(): void
    {
        $responsibleA = Responsible::factory()->create();
        $ticketA = Ticket::factory()->create([
            'responsible_id' => $responsibleA->id,
        ]);
        
        $assigned = (new LeastBusyAgentStrategy())->assign($ticketA);

        $this->assertSame($responsibleA->id, $assigned->responsible_id);
        $this->assertSame($responsibleA->id, $ticketA->fresh()->responsible_id);
    }
}
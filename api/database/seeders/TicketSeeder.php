<?php

namespace Database\Seeders;

use App\Models\Responsible;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $responsibles = Responsible::orderBy('id')->take(2)->get();

        Ticket::create([
            'title' => 'Sample Ticket 1',
            'description' => 'This is a sample ticket.',
            'status' => 'open',
            'priority' => 'high',
            'employee_email' => 'john.doe@example.com',
            'responsible_id' => $responsibles[0]->id,
        ]);

        Ticket::create([
            'title' => 'Sample Ticket 2',
            'description' => 'This is another sample ticket.',
            'status' => 'open',
            'priority' => 'medium',
            'employee_email' => 'jane.smith@example.com',
            'responsible_id' => $responsibles[1]->id,
        ]);
    }
}

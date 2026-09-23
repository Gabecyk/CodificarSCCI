<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ticket::create([
            'title' => 'Sample Ticket 1',
            'description' => 'This is a sample ticket.',
            'status' => 'open',
            'priority' => 'high',
            'employee_email' => 'john.doe@example.com',
            'responsible_id' => 1
        ]);

        Ticket::create([
            'title' => 'Sample Ticket 2',
            'description' => 'This is another sample ticket.',
            'status' => 'open',
            'priority' => 'medium',
            'employee_email' => 'jane.smith@example.com',
            'responsible_id' => 2
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Responsible;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // O container roda o seed a cada subida sem duplicar dados.
        if (Responsible::exists()) {
            return;
        }

        $this->call(ResponsibleSeeder::class);
        $this->call(TicketSeeder::class);
    }
}

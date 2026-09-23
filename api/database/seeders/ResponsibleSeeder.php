<?php

namespace Database\Seeders;

use App\Models\Responsible;
use Illuminate\Database\Seeder;

class ResponsibleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Responsible::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com'
        ]);

        Responsible::create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com'
        ]);

        Responsible::create([
            'name' => 'Alice Johnson',
            'email' => 'alice.johnson@example.com'
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Responsible;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Responsible>
 */
class ResponsibleFactory extends Factory
{
    protected $model = Responsible::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}

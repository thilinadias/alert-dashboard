<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Alert>
 */
class AlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'severity' => $this->faker->randomElement(['critical', 'warning', 'info', 'default']),
            'status' => 'new',
            'client_id' => \App\Models\Client::factory(),
            'device' => $this->faker->word,
            'ticket_number' => $this->faker->bothify('TKT-####'),
        ];
    }
}

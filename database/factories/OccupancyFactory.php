<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class OccupancyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'salary_amount' => fake()->numberBetween(1500, 10000),
            'salary_currency' => fake()->randomElement(Currency::cases()),
            'starred' => fake()->boolean(),
            'started_at' => fake()->dateTimeBetween('-10 years', '-13 months'),
            'ended_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}

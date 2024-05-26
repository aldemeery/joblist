<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(asText: true),
            'description' => fake()->sentence(),
            'quantity' => fake()->numberBetween(1, 5),
            'rate' => fake()->randomFloat(2, 100, 5000),
        ];
    }
}

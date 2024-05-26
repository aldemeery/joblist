<?php

declare(strict_types=1);

namespace Database\Factories;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'number' => fake()->unique()->randomNumber(3),
            'date' => $date = new CarbonImmutable(fake()->dateTimeThisMonth()),
            'net_terms' => $terms = fake()->randomDigitNotNull(),
            'due_date' => $date->addDays($terms),
            'issuer_address_line_one' => fake()->name(),
            'issuer_address_line_two' => fake()->streetAddress(),
            'issuer_address_line_three' => sprintf(
                '%s, %s %s',
                fake()->city(),
                fake()->stateAbbr(),
                fake()->postcode(),
            ),
            'issuer_address_line_four' => fake()->country(),
            'client_address_line_one' => fake()->name(),
            'client_address_line_two' => fake()->streetAddress(),
            'client_address_line_three' => sprintf(
                '%s, %s %s',
                fake()->city(),
                fake()->stateAbbr(),
                fake()->postcode(),
            ),
            'client_address_line_four' => fake()->country(),
            'tax' => fake()->randomFloat(2, 1, 20),
            'discount' => fake()->numberBetween(10, 90),
            'payment_details' => sprintf(
                'Please send the money via Wise to: %s',
                fake()->safeEmail(),
            ),
            'notes' => fake()->realText(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'website' => $website = sprintf('https://%s', fake()->unique()->domainName()),
            'jobs_page' => sprintf('%s/career', $website),
            'remote' => fake()->boolean(),
        ];
    }
}

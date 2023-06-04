<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Airport>
 */
class AirportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'icao' => $this->faker->unique()->regexify('[A-Z]{4}'),
            'name' => $this->faker->unique()->city() . ' Airport',
            'runways' => $this->faker->unique()->regexify('[0-9]{2}[LRC]'),
        ];
    }
}

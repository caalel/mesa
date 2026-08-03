<?php

namespace Database\Factories;

use App\Models\Food;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Food>
 */
class FoodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_pt' => $this->faker->unique()->words(2, true),
            'name_en' => $this->faker->unique()->words(2, true),
            'calories_per_100g' => 100,
            'protein_per_100g' => 1,
            'carbs_per_100g' => 20,
            'fat_per_100g' => 1,
            'data_source' => 'taco',
            'source_code' => $this->faker->unique()->uuid(),
            'source_version' => '4',
        ];
    }
}

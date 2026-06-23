<?php

use App\Models\Food;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns only foods matching the search query through HTTP', function () {
    Food::factory()->create([
        'name' => 'Banana',
        'calories_per_100g' => 89,
        'protein_per_100g' => 1.1,
        'carbs_per_100g' => 22.8,
        'fat_per_100g' => 0.3,
    ]);

    Food::factory()->create([
        'name' => 'Maçã',
        'calories_per_100g' => 52,
        'protein_per_100g' => 0.3,
        'carbs_per_100g' => 13.8,
        'fat_per_100g' => 0.2,
    ]);

    $response = $this->get('/foods/search?query=Ban');

    $response
        ->assertOk()
        ->assertSee('Banana')
        ->assertDontSee('Maçã');
});

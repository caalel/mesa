<?php

use App\Models\Food;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('compares two foods through HTTP', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'calories_per_100g' => 89,
    ]);

    $maca = Food::factory()->create([
        'name_pt' => 'Maçã',
        'calories_per_100g' => 52,
    ]);

    $response = $this->post('/compare', [
        'food_a_id' => $banana->id,
        'food_a_weight' => 100,
        'food_b_id' => $maca->id,
    ]);

    $response
        ->assertOk()
        ->assertSee('171.15');
});

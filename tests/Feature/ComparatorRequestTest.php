<?php

use App\Models\Food;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns 422 when food_a_id is missing', function () {
    $payload = validComparatorPayload();

    unset($payload['food_a_id']);

    $this->post('/compare', $payload)
        ->assertUnprocessable()
        ->assertInvalid('food_a_id');
});

it('returns 422 when food_b_id is missing', function () {
    $payload = validComparatorPayload();

    unset($payload['food_b_id']);

    $this->post('/compare', $payload)
        ->assertUnprocessable()
        ->assertInvalid('food_b_id');
});

it('returns 422 when food_a_weight is missing', function () {
    $payload = validComparatorPayload();

    unset($payload['food_a_weight']);

    $this->post('/compare', $payload)
        ->assertUnprocessable()
        ->assertInvalid('food_a_weight');
});

it('returns 422 when food_a_weight is not numeric', function () {
    $payload = validComparatorPayload([
        'food_a_weight' => 'not numeric',
    ]);

    $this->post('/compare', $payload)
        ->assertUnprocessable()
        ->assertInvalid('food_a_weight');
});

it('returns 422 when food_a_weight is zero', function () {
    $payload = validComparatorPayload([
        'food_a_weight' => 0,
    ]);

    $this->post('/compare', $payload)
        ->assertUnprocessable()
        ->assertInvalid('food_a_weight');
});

it('returns 422 when food_a_weight is less than zero', function () {
    $payload = validComparatorPayload([
        'food_a_weight' => -1,
    ]);

    $this->post('/compare', $payload)
        ->assertUnprocessable()
        ->assertInvalid('food_a_weight');
});

it('returns 422 when food_a_id does not exist', function () {
    $payload = validComparatorPayload([
        'food_a_id' => 999999,
    ]);

    $this->post('/compare', $payload)
        ->assertUnprocessable()
        ->assertInvalid('food_a_id');
});

it('returns 422 when food_b_id does not exist', function () {
    $payload = validComparatorPayload([
        'food_b_id' => 999999,
    ]);

    $this->post('/compare', $payload)
        ->assertUnprocessable()
        ->assertInvalid('food_b_id');
});

function validComparatorPayload(array $overrides = []): array
{
    $foodA = Food::factory()->create([
        'name' => 'Banana',
        'calories_per_100g' => 89,
        'protein_per_100g' => 0,
        'carbs_per_100g' => 0,
        'fat_per_100g' => 0,
    ]);

    $foodB = Food::factory()->create([
        'name' => 'Apple',
        'calories_per_100g' => 52,
        'protein_per_100g' => 0,
        'carbs_per_100g' => 0,
        'fat_per_100g' => 0,
    ]);

    return array_merge([
        'food_a_id' => $foodA->id,
        'food_b_id' => $foodB->id,
        'food_a_weight' => 100,
    ], $overrides);
}

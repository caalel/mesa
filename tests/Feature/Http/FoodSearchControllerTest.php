<?php

use App\Models\Food;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns only foods matching the search query through HTTP', function () {
    Food::factory()->create([
        'name_pt' => 'Banana',
    ]);

    Food::factory()->create([
        'name_pt' => 'Maçã',
    ]);

    $response = $this->get('/foods/search?query=Ban');

    $response
        ->assertOk()
        ->assertSee('Banana')
        ->assertDontSee('Maçã');
});

it('returns unprocessable entity when the search query is missing', function () {
    $this->get('/foods/search')
        ->assertUnprocessable()
        ->assertInvalid('query');
});

it('returns unprocessable entity when the search query is empty', function () {
    $this->get('/foods/search?query=')
        ->assertUnprocessable()
        ->assertInvalid('query');
});

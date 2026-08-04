<?php

use App\Models\Food;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns only foods matching the search query through HTTP', function () {
    Food::factory()->create([
        'name_pt' => 'Banana',
        'name_en' => 'Banana',
    ]);

    Food::factory()->create([
        'name_pt' => 'Maçã',
        'name_en' => 'Apple',
    ]);

    $response = $this->withHeader('Accept-Language', 'pt-BR')->get('/foods/search?query=Ban');

    $response
        ->assertOk()
        ->assertSee('Banana')
        ->assertDontSee('Maçã');
});

it('returns localized English food names through HTTP', function () {
    Food::factory()->create([
        'name_pt' => 'Maçã gala',
        'name_en' => 'Gala apple',
    ]);

    $response = $this->withHeader('Accept-Language', 'en')->get('/foods/search?query=Gala');

    $response
        ->assertOk()
        ->assertSee('Gala apple')
        ->assertDontSee('Maçã gala');
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

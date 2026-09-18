<?php

use Illuminate\Support\Facades\App;

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->withoutVite();
});

/*
|--------------------------------------------------------------------------
| Tests
|--------------------------------------------------------------------------
*/

it('renders the nutritional comparator initial screen', function () {
    $response = $this->get('/comparator');

    $response
        ->assertOk()
        ->assertSee(__('ui.compare.title'))
        ->assertSee(__('ui.compare.subtitle'));
});

it('renders the comparison nutrient selector in Brazilian Portuguese', function () {
    App::setLocale('pt_BR');

    $response = $this->withSession(['locale' => 'pt_BR'])->get('/comparator');

    $response
        ->assertOk()
        ->assertSee('Comparar por')
        ->assertSee('Calorias')
        ->assertSee('Proteínas')
        ->assertSee('Carboidratos')
        ->assertSee('Gorduras');
});

it('renders the nutritional comparator interface in English', function () {
    $response = $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->get('/comparator');

    $response
        ->assertOk()
        ->assertSeeHtml('<html lang="en">')
        ->assertSee('Compare foods')
        ->assertSee('Find out how much of one food is equivalent to another in calories.')
        ->assertSee('Reference food')
        ->assertSee('Food to compare')
        ->assertSee('Compare')
        ->assertSee('Compare by')
        ->assertSee('Calories')
        ->assertSee('Protein')
        ->assertSee('Carbohydrates')
        ->assertSee('Fat');
});

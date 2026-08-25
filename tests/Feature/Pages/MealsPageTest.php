<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

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

it('has a named meals route', function () {
    expect(Route::has('meals'))->toBeTrue();
});

it('renders the meals initial screen in Brazilian Portuguese', function () {
    App::setLocale('pt_BR');

    $response = $this->withSession(['locale' => 'pt_BR'])->get('/meals');

    $response
        ->assertOk()
        ->assertSeeHtml('data-testid="meals-page"')
        ->assertSee('Refeições')
        ->assertSee('Monte refeições e acompanhe calorias e macronutrientes em tempo real.');
});

it('renders the meals initial screen in English', function () {
    $response = $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->get('/meals');

    $response
        ->assertOk()
        ->assertSeeHtml('<html lang="en">')
        ->assertSeeHtml('data-testid="meals-page"')
        ->assertSee('Meals')
        ->assertSee('Build meals and track calories and macronutrients in real time.');
});

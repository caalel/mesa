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

it('has a named home route', function () {
    expect(Route::has('home'))->toBeTrue();
});

it('responds successfully at the root path', function () {
    $this->get('/')
        ->assertOk();
});

it('renders a dedicated homepage', function () {
    $this->get('/')
        ->assertOk()
        ->assertSeeHtml('data-testid="homepage"');
});

it('links to the available tools', function () {
    $this->get('/')
        ->assertOk()
        ->assertSeeHtml('data-testid="open-comparator"')
        ->assertSeeHtml('href="'.route('comparator').'"')
        ->assertSeeHtml('data-testid="open-meals"')
        ->assertSeeHtml('href="'.route('meals').'"');
});

it('renders the planned homepage content in Brazilian Portuguese', function () {
    App::setLocale('pt_BR');

    $response = $this->withSession(['locale' => 'pt_BR'])->get('/');

    $response
        ->assertOk()
        ->assertSee('MESA')
        ->assertSee('Medidor de Equivalência e Síntese Alimentar')
        ->assertSee('Compare alimentos, monte refeições e entenda melhor as relações entre calorias e macronutrientes.')
        ->assertSee('Ferramentas')
        ->assertSee('Comparador nutricional')
        ->assertSee('Compare alimentos e descubra quantidades equivalentes com base em calorias, proteínas, carboidratos ou gorduras.')
        ->assertSee('Abrir comparador')
        ->assertSeeHtml('data-testid="homepage"')
        ->assertSeeHtml('data-testid="comparator-tool-card"')
        ->assertSeeHtml('data-testid="open-comparator"')
        ->assertSeeHtml('href="'.route('comparator').'"');
});

it('renders the planned homepage content in English', function () {
    $response = $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->get('/');

    $response
        ->assertOk()
        ->assertSeeHtml('<html lang="en">')
        ->assertSee('MESA')
        ->assertSee('Medidor de Equivalência e Síntese Alimentar')
        ->assertSee('Compare foods, build meals, and better understand the relationships between calories and macronutrients.')
        ->assertSee('Tools')
        ->assertSee('Nutritional comparator')
        ->assertSee('Compare foods and find equivalent amounts based on calories, protein, carbohydrates, or fat.')
        ->assertSee('Open comparator')
        ->assertSeeHtml('data-testid="homepage"')
        ->assertSeeHtml('data-testid="comparator-tool-card"')
        ->assertSeeHtml('data-testid="open-comparator"')
        ->assertSeeHtml('href="'.route('comparator').'"');
});

it('has a named comparator route', function () {
    expect(Route::has('comparator'))->toBeTrue();
});

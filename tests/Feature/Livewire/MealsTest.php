<?php

use App\Livewire\Meals;
use App\Models\Food;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

uses(RefreshDatabase::class);

beforeEach(function () {
    App::setLocale('pt_BR');
});

/*
|--------------------------------------------------------------------------
| Tests
|--------------------------------------------------------------------------
*/

it('shows the initial empty state without an open meal editor', function () {
    Livewire::test(Meals::class)
        ->assertSet('isMealEditorOpen', false)
        ->assertSeeHtml('data-testid="meals-empty-state"')
        ->assertSeeHtml('data-testid="create-meal"')
        ->assertDontSeeHtml('data-testid="meal-editor"');
});

it('opens the editor for a new meal', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->assertSet('isMealEditorOpen', true)
        ->assertSet('editingMealId', null)
        ->assertDontSeeHtml('data-testid="meals-empty-state"')
        ->assertSeeHtml('data-testid="meal-editor"')
        ->assertSeeHtml('data-testid="meal-name"')
        ->assertSeeHtml('data-testid="submit-meal"');
});

it('starts a new meal without temporary items', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->assertSet('isMealEditorOpen', true)
        ->assertSet('mealItems', []);
});

it('renders the food modal trigger in the meal editor', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->assertSeeHtml('data-testid="open-food-modal"')
        ->assertSeeHtml('wire:click="openFoodModal"');
});

it('opens the food modal', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->assertSet('isFoodModalOpen', true);
});

it('closes the food modal without closing the meal editor', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('closeFoodModal')
        ->assertSet('isFoodModalOpen', false)
        ->assertSet('isMealEditorOpen', true);
});

it('renders the food modal close control', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->assertSeeHtml('data-testid="close-food-modal"')
        ->assertSeeHtml('wire:click="closeFoodModal"');
});

it('renders the food modal only after opening it', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->assertDontSeeHtml('data-testid="food-modal"')
        ->call('openFoodModal')
        ->assertSeeHtml('data-testid="food-modal"');
});

it('renders the food search field with the expected Livewire binding in the open food modal', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->assertSet('foodSearch', '')
        ->assertSeeHtml('data-testid="food-search"')
        ->assertSeeHtml('wire:model.live.debounce.300ms="foodSearch"');
});

it('renders the food search placeholder in Brazilian Portuguese', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->assertSeeHtml('placeholder="Digite o nome do alimento"');
});

it('renders the food search placeholder in English', function () {
    App::setLocale('en');

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->assertSeeHtml('placeholder="Type the food name"');
});

it('shows Portuguese food search results after the first character is entered', function () {
    Food::factory()->create([
        'name_pt' => 'Arroz integral',
        'name_en' => 'Brown rice',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->assertDontSee('Arroz integral')
        ->set('foodSearch', 'A')
        ->assertSee('Arroz integral');
});

it('shows English food search results after the first character is entered', function () {
    App::setLocale('en');

    Food::factory()->create([
        'name_pt' => 'Arroz integral',
        'name_en' => 'Brown rice',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->assertDontSee('Brown rice')
        ->set('foodSearch', 'B')
        ->assertSee('Brown rice');
});

it('shows the results heading in Brazilian Portuguese when food search results are visible', function () {
    Food::factory()->create([
        'name_pt' => 'Arroz integral',
        'name_en' => 'Brown rice',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->set('foodSearch', 'A')
        ->assertSee('Resultados');
});

it('shows the results heading in English when food search results are visible', function () {
    App::setLocale('en');

    Food::factory()->create([
        'name_pt' => 'Arroz integral',
        'name_en' => 'Brown rice',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->set('foodSearch', 'B')
        ->assertSee('Results');
});

it('shows calories per 100 grams for a food search result', function () {
    Food::factory()->create([
        'name_pt' => 'Arroz integral',
        'name_en' => 'Brown rice',
        'calories_per_100g' => 124,
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->set('foodSearch', 'Arroz')
        ->assertSee('124 kcal / 100 g');
});

it('shows the food search empty state without food results when a non-empty search has no matches', function () {
    Food::factory()->create([
        'name_pt' => 'Banana',
        'name_en' => 'Banana',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->assertDontSeeHtml('data-testid="food-search-empty"')
        ->set('foodSearch', 'X')
        ->assertSeeHtml('data-testid="food-search-empty"')
        ->assertDontSee('Banana');
});

it('does not show the food search empty state for a whitespace-only search', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->set('foodSearch', '   ')
        ->assertDontSeeHtml('data-testid="food-search-empty"');
});

it('renders the food search empty state in Brazilian Portuguese', function () {
    Food::factory()->create([
        'name_pt' => 'Banana',
        'name_en' => 'Banana',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->set('foodSearch', 'X')
        ->assertSee('Nenhum alimento encontrado')
        ->assertSee('Tente buscar por outro nome ou termo.');
});

it('renders the food search empty state in English', function () {
    App::setLocale('en');

    Food::factory()->create([
        'name_pt' => 'Banana',
        'name_en' => 'Banana',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->set('foodSearch', 'X')
        ->assertSee('No foods found')
        ->assertSee('Try searching for another name or term.');
});

it('cancels the new meal editor', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->set('mealName', 'Temporary meal')
        ->assertSet('isMealEditorOpen', true)
        ->assertSet('mealName', 'Temporary meal')
        ->assertSeeHtml('data-testid="meal-editor"')
        ->call('cancelMealEditor')
        ->assertSet('isMealEditorOpen', false)
        ->assertSet('editingMealId', null)
        ->assertSet('mealName', '')
        ->assertDontSeeHtml('data-testid="meal-editor"')
        ->assertSeeHtml('data-testid="create-meal"');
});

it('discards temporary items when cancelling the meal editor', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->set('mealItems', [['temporary' => true]])
        ->assertNotSet('mealItems', [])
        ->call('cancelMealEditor')
        ->assertSet('isMealEditorOpen', false)
        ->assertSet('editingMealId', null)
        ->assertSet('mealName', '')
        ->assertSet('mealItems', []);
});

it('renders the empty state and new meal editor in Brazilian Portuguese', function () {
    $component = Livewire::test(Meals::class);

    $component
        ->assertSee('Nenhuma refeição criada ainda.')
        ->assertSee('Crie uma refeição para começar a organizar seus alimentos e acompanhar os totais nutricionais.')
        ->assertSee('Criar refeição');

    $component
        ->call('createMeal')
        ->assertSee('Nova refeição')
        ->assertSee('Criar refeição')
        ->assertSeeHtml('data-testid="meal-editor"')
        ->assertSeeHtml('data-testid="meal-name"')
        ->assertSeeHtml('data-testid="submit-meal"');
});

it('renders the empty state and new meal editor in English', function () {
    App::setLocale('en');

    $component = Livewire::test(Meals::class);

    $component
        ->assertSee('No meals created yet.')
        ->assertSee('Create a meal to start organizing your foods and tracking nutritional totals.')
        ->assertSee('Create meal');

    $component
        ->call('createMeal')
        ->assertSee('New meal')
        ->assertSee('Create meal')
        ->assertSeeHtml('data-testid="meal-editor"')
        ->assertSeeHtml('data-testid="meal-name"')
        ->assertSeeHtml('data-testid="submit-meal"');
});

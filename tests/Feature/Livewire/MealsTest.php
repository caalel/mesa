<?php

use App\Livewire\Meals;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

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

it('renders the food modal only after opening it', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->assertDontSeeHtml('data-testid="food-modal"')
        ->call('openFoodModal')
        ->assertSeeHtml('data-testid="food-modal"');
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

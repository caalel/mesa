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
    App::setLocale('pt_BR');

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
    App::setLocale('pt_BR');

    $food = Food::factory()->create([
        'name_pt' => 'Arroz integral',
        'name_en' => 'Brown rice',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->assertDontSeeHtml('data-testid="select-food-'.$food->id.'"')
        ->set('foodSearch', 'A')
        ->assertSeeHtml('data-testid="select-food-'.$food->id.'"');
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
    App::setLocale('pt_BR');

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

it('keeps the food search results visible after selecting a food', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'name_en' => 'Banana',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->set('foodSearch', 'B')
        ->assertSeeHtml('data-testid="select-food-'.$banana->id.'"')
        ->assertSeeHtml('wire:click="selectFood('.$banana->id.')"')
        ->call('selectFood', $banana->id)
        ->assertSet('foodSearch', 'B')
        ->assertSeeHtml('data-testid="select-food-'.$banana->id.'"')
        ->assertSeeHtml('wire:click="selectFood('.$banana->id.')"');
});

it('shows the selected food details after selecting a food', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'name_en' => 'Banana',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->assertSet('selectedFoodId', null)
        ->assertSet('foodWeight', '')
        ->assertDontSeeHtml('data-testid="selected-food-details"')
        ->call('selectFood', $banana->id)
        ->assertSet('selectedFoodId', $banana->id)
        ->assertSet('foodWeight', '100')
        ->assertSeeHtml('data-testid="selected-food-details"');
});

it('resets the food weight when selecting another food', function () {
    $apple = Food::factory()->create([
        'name_pt' => 'Maçã',
        'name_en' => 'Apple',
    ]);
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'name_en' => 'Banana',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->set('foodSearch', 'A')
        ->call('selectFood', $apple->id)
        ->set('foodWeight', '250')
        ->call('selectFood', $banana->id)
        ->assertSet('selectedFoodId', $banana->id)
        ->assertSet('foodWeight', '100');
});

it('renders the selected food weight input with an explicit debounce', function () {
    $food = Food::factory()->create();

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->assertSeeHtml('data-testid="food-weight"')
        ->assertSeeHtml('wire:model.live.debounce.300ms="foodWeight"');
});

it('shows the nutrition preview for the selected food weight', function () {
    $food = Food::factory()->create([
        'name_pt' => 'Alimento nutricional',
        'name_en' => 'Nutritional food',
        'calories_per_100g' => 121,
        'protein_per_100g' => 13,
        'carbs_per_100g' => 17,
        'fat_per_100g' => 19,
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->assertSeeHtml('data-testid="selected-food-nutrition-preview"')
        ->assertSee('121 kcal')
        ->assertSee('13 g')
        ->assertSee('17 g')
        ->assertSee('19 g');
});

it('updates the nutrition preview when the food weight changes', function () {
    $food = Food::factory()->create([
        'calories_per_100g' => 121,
        'protein_per_100g' => 13,
        'carbs_per_100g' => 17,
        'fat_per_100g' => 19,
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->assertSee('121 kcal')
        ->assertSee('13 g')
        ->assertSee('17 g')
        ->assertSee('19 g')
        ->set('foodWeight', '200')
        ->assertDontSee('121 kcal')
        ->assertSee('242 kcal')
        ->assertSee('26 g')
        ->assertSee('34 g')
        ->assertSee('38 g');
});

it('shows the selected food details copy in Brazilian Portuguese', function () {
    App::setLocale('pt_BR');

    $food = Food::factory()->create([
        'name_pt' => 'Alimento nutricional',
        'name_en' => 'Nutritional food',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->assertSee('Alimento nutricional')
        ->assertSee('Quantidade')
        ->assertSee('Para 100 g')
        ->assertSee('Proteínas')
        ->assertSee('Carboidratos')
        ->assertSee('Gorduras');
});

it('shows the selected food details copy in English', function () {
    App::setLocale('en');

    $food = Food::factory()->create([
        'name_pt' => 'Alimento nutricional',
        'name_en' => 'Nutritional food',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->assertSee('Nutritional food')
        ->assertSee('Amount')
        ->assertSee('For 100 g')
        ->assertSee('Protein')
        ->assertSee('Carbohydrates')
        ->assertSee('Fat');
});

it('shows the selected food badge in Brazilian Portuguese', function () {
    App::setLocale('pt_BR');

    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'name_en' => 'Banana',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->set('foodSearch', 'B')
        ->call('selectFood', $banana->id)
        ->assertSee('Selecionado');
});

it('shows the selected food badge in English', function () {
    App::setLocale('en');

    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'name_en' => 'Banana',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->set('foodSearch', 'B')
        ->call('selectFood', $banana->id)
        ->assertSee('Selected');
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
        ->assertSeeHtml('data-testid="food-search-empty"');
});

it('does not show the food search empty state for a whitespace-only search', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->set('foodSearch', '   ')
        ->assertDontSeeHtml('data-testid="food-search-empty"');
});

it('renders the food search empty state in Brazilian Portuguese', function () {
    App::setLocale('pt_BR');

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
    App::setLocale('pt_BR');

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

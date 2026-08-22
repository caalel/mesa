<?php

use App\Livewire\Meals;
use App\Models\Food;
use App\Services\FoodWeightInputService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

uses(RefreshDatabase::class);

function mountMealEditorWithDraft(array $mealItems): mixed
{
    return Livewire::test(Meals::class, [
        'isMealEditorOpen' => true,
        'mealItems' => $mealItems,
    ]);
}


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

it('rejects client-side updates to meal items', function () {
    expect(fn () => Livewire::test(Meals::class)
        ->set('mealItems', [['food_id' => 1, 'weight' => 100.0]]))
        ->toThrow(CannotUpdateLockedPropertyException::class, 'Cannot update locked property: [mealItems]');
});

it('keeps the meal items empty state for a draft without items regardless of its name', function () {
    $component = Livewire::test(Meals::class)
        ->call('createMeal')
        ->assertSeeHtml('data-testid="meal-items-empty-state"')
        ->assertDontSeeHtml('data-testid="meal-items-list"');

    $component
        ->set('mealName', 'Lunch')
        ->assertSeeHtml('data-testid="meal-items-empty-state"')
        ->assertDontSeeHtml('data-testid="meal-items-list"');
});

it('renders a localized food item from the meal draft', function () {
    App::setLocale('pt_BR');

    $food = Food::factory()->create([
        'name_pt' => 'Brócolis cozido',
        'name_en' => 'Cooked broccoli',
    ]);

    $mealItems = [
        ['food_id' => $food->id, 'weight' => 50.5],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->assertSeeHtml('data-testid="meal-items-list"')
        ->assertSeeHtml('data-testid="meal-item"')
        ->assertSeeHtml('data-food-id="'.$food->id.'"')
        ->assertSee('Brócolis cozido');
});

it('renders localized nutritional values and macro labels calculated for a meal draft item weight', function () {
    App::setLocale('pt_BR');

    $food = Food::factory()->create([
        'calories_per_100g' => 123,
        'protein_per_100g' => 12.34,
        'carbs_per_100g' => 45.67,
        'fat_per_100g' => 8.9,
    ]);

    $mealItems = [
        ['food_id' => $food->id, 'weight' => 50.5],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->assertSee('50,5 g')
        ->assertSee('62,12 kcal')
        ->assertSee('6,23 g')
        ->assertSee(__('ui.meals.protein'))
        ->assertSee('23,06 g')
        ->assertSee(__('ui.meals.carbohydrates'))
        ->assertSee('4,49 g')
        ->assertSee(__('ui.meals.fat'));
});

it('shows the localized Portuguese meal items counter for empty and populated drafts', function () {
    App::setLocale('pt_BR');

    $foods = Food::factory()->count(2)->create();
    $mealItems = $foods
        ->map(fn (Food $food) => ['food_id' => $food->id, 'weight' => 100.0])
        ->all();

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->assertSee('0 de 10 alimentos');

    $component = mountMealEditorWithDraft($mealItems);

    $component->assertSee('2 de 10 alimentos');
});

it('shows the localized English meal items counter for empty and populated drafts', function () {
    App::setLocale('en');

    $foods = Food::factory()->count(2)->create();
    $mealItems = $foods
        ->map(fn (Food $food) => ['food_id' => $food->id, 'weight' => 100.0])
        ->all();

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->assertSee('0 of 10 foods');

    $component = mountMealEditorWithDraft($mealItems);

    $component->assertSee('2 of 10 foods');
});

it('keeps the food modal trigger enabled below the meal item limit', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->assertSeeHtml('data-testid="open-food-modal-enabled"')
        ->assertSeeHtml('wire:click="openFoodModal"')
        ->assertDontSeeHtml('data-testid="open-food-modal-disabled"');
});

it('disables the food modal trigger at the meal item limit', function () {
    $mealItems = Food::factory()->count(10)->create()
        ->map(fn (Food $food) => ['food_id' => $food->id, 'weight' => 100.0])
        ->all();

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->assertSeeHtml('data-testid="open-food-modal-disabled"')
        ->assertSeeHtml('disabled')
        ->assertDontSeeHtml('wire:click="openFoodModal"')
        ->assertDontSeeHtml('data-testid="open-food-modal-enabled"');
});

it('does not open the food modal directly at the meal item limit', function () {
    $mealItems = Food::factory()->count(10)->create()
        ->map(fn (Food $food) => ['food_id' => $food->id, 'weight' => 100.0])
        ->all();

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('openFoodModal')
        ->assertSet('isFoodModalOpen', false)
        ->assertSet('editingMealItemFoodId', null);
});

it('does not add an eleventh distinct food when add food is called directly at the meal item limit', function () {
    $foods = Food::factory()->count(11)->create();
    $mealItems = $foods->take(10)
        ->map(fn (Food $food) => ['food_id' => $food->id, 'weight' => 100.0])
        ->all();
    $eleventhFood = $foods->last();

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->set('foodSearch', 'Pending search')
        ->call('selectFood', $eleventhFood->id)
        ->set('foodWeight', '250')
        ->call('addFoodToDraft')
        ->assertSet('mealItems', $mealItems)
        ->assertSet('foodSearch', 'Pending search')
        ->assertSet('selectedFoodId', $eleventhFood->id)
        ->assertSet('foodWeight', '250');
});

it('merges a duplicate food when add food is called directly at the meal item limit', function () {
    $foods = Food::factory()->count(10)->create();
    $mealItems = $foods
        ->map(fn (Food $food) => ['food_id' => $food->id, 'weight' => 100.0])
        ->all();
    $duplicateFood = $foods->first();
    $expectedMealItems = $mealItems;
    $expectedMealItems[0]['weight'] = 125.0;

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('selectFood', $duplicateFood->id)
        ->set('foodWeight', '25')
        ->call('addFoodToDraft')
        ->assertSet('mealItems', $expectedMealItems);
});

it('reenables the food modal trigger after removing an item at the meal item limit', function () {
    $mealItems = Food::factory()->count(10)->create()
        ->map(fn (Food $food) => ['food_id' => $food->id, 'weight' => 100.0])
        ->all();

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('removeMealItem', $mealItems[0]['food_id'])
        ->assertSet('mealItems', array_slice($mealItems, 1))
        ->assertSeeHtml('data-testid="open-food-modal-enabled"')
        ->assertSeeHtml('wire:click="openFoodModal"')
        ->assertDontSeeHtml('data-testid="open-food-modal-disabled"');
});

it('cancels the food modal and discards its temporary state without closing the meal editor', function () {
    $banana = Food::factory()->create();
    $draftFood = Food::factory()->create();
    $mealItems = [
        ['food_id' => $draftFood->id, 'weight' => 100.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->set('mealName', 'Lunch')
        ->call('openFoodModal')
        ->set('foodSearch', 'Banana')
        ->call('selectFood', $banana->id)
        ->set('foodWeight', '250')
        ->call('cancelFoodModal')
        ->assertSet('isFoodModalOpen', false)
        ->assertSet('isMealEditorOpen', true)
        ->assertSet('foodSearch', '')
        ->assertSet('selectedFoodId', null)
        ->assertSet('foodWeight', '')
        ->assertSet('mealName', 'Lunch')
        ->assertSet('mealItems', $mealItems);
});

it('renders the food modal close control', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->assertSeeHtml('data-testid="close-food-modal"')
        ->assertSeeHtml('wire:click="cancelFoodModal"');
});

it('renders the food modal footer cancel control', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->assertSeeHtml('data-testid="cancel-food-modal"')
        ->assertSeeHtml('wire:click="cancelFoodModal"');
});

it('opens and renders the food modal only after opening it', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->assertDontSeeHtml('data-testid="food-modal"')
        ->call('openFoodModal')
        ->assertSet('isFoodModalOpen', true)
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

it('renders the localized food search placeholder', function (string $locale, string $placeholder) {
    App::setLocale($locale);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->assertSeeHtml('placeholder="'.$placeholder.'"');
})->with([
    'Brazilian Portuguese' => ['pt_BR', 'Digite o nome do alimento'],
    'English' => ['en', 'Type the food name'],
]);

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

it('shows the localized results heading when food search results are visible', function (string $locale, string $search, string $heading) {
    App::setLocale($locale);

    Food::factory()->create([
        'name_pt' => 'Arroz integral',
        'name_en' => 'Brown rice',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->set('foodSearch', $search)
        ->assertSee($heading);
})->with([
    'Brazilian Portuguese' => ['pt_BR', 'A', 'Resultados'],
    'English' => ['en', 'B', 'Results'],
]);

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

it('dispatches an event when the selected food details are shown', function () {
    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'name_en' => 'Banana',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $banana->id)
        ->assertDispatched('selected-food-details-shown');
});

it('does not select an unavailable food', function () {
    $draftFood = Food::factory()->create();
    $unavailableFood = Food::factory()->create();
    $unavailableFoodId = $unavailableFood->id;
    $unavailableFood->delete();

    $mealItems = [
        ['food_id' => $draftFood->id, 'weight' => 100.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->set('foodWeight', '250')
        ->call('selectFood', $unavailableFoodId)
        ->assertSet('mealItems', [
            ['food_id' => $draftFood->id, 'weight' => 100.0],
        ])
        ->assertSet('selectedFoodId', null)
        ->assertSet('foodWeight', '250')
        ->assertNotDispatched('selected-food-details-shown');
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

it('keeps the add food to draft button disabled until a food is selected', function () {
    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->assertSeeHtml('data-testid="add-food-to-draft-disabled"')
        ->assertSeeHtml('disabled')
        ->assertDontSeeHtml('wire:click="addFoodToDraft"');
});

it('keeps the add food to draft button disabled for an invalid food weight', function () {
    $food = Food::factory()->create();

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->set('foodWeight', '0')
        ->assertSeeHtml('data-testid="add-food-to-draft-disabled"')
        ->assertSeeHtml('disabled')
        ->assertDontSeeHtml('wire:click="addFoodToDraft"');
});

it('enables the add food to draft button for a selected food with a valid weight', function () {
    $food = Food::factory()->create();

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->set('foodWeight', '250')
        ->assertSeeHtml('data-testid="add-food-to-draft-enabled"')
        ->assertSeeHtml('wire:click="addFoodToDraft"')
        ->assertDontSeeHtml('data-testid="add-food-to-draft-disabled"');
});

it('adds a valid food to the meal draft and resets the food modal', function () {
    $food = Food::factory()->create();

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->set('foodSearch', 'Pending search')
        ->call('selectFood', $food->id)
        ->set('foodWeight', '250')
        ->assertSeeHtml('data-testid="meal-items-empty-state"')
        ->assertDontSeeHtml('data-testid="meal-items-list"')
        ->assertSee('0 de 10 alimentos')
        ->call('addFoodToDraft')
        ->assertSet('mealItems', [
            [
                'food_id' => $food->id,
                'weight' => 250.0,
            ],
        ])
        ->assertSet('isFoodModalOpen', false)
        ->assertSet('foodSearch', '')
        ->assertSet('selectedFoodId', null)
        ->assertSet('foodWeight', '')
        ->assertSet('isMealEditorOpen', true)
        ->assertDontSeeHtml('data-testid="meal-items-empty-state"')
        ->assertSeeHtml('data-testid="meal-items-list"')
        ->assertSee('1 de 10 alimentos');
});

it('stores a comma decimal food weight normalized in the meal draft', function () {
    $food = Food::factory()->create();

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->set('foodWeight', '12,5')
        ->call('addFoodToDraft')
        ->assertSet('mealItems', [
            [
                'food_id' => $food->id,
                'weight' => 12.5,
            ],
        ]);
});

it('adds a duplicate food weight to its original draft item while preserving distinct food order and count', function () {
    App::setLocale('pt_BR');

    $foodA = Food::factory()->create();
    $foodB = Food::factory()->create();

    $mealItems = [
        ['food_id' => $foodA->id, 'weight' => 100.0],
        ['food_id' => $foodB->id, 'weight' => 50.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('openFoodModal')
        ->call('selectFood', $foodA->id)
        ->set('foodWeight', '25.5')
        ->call('addFoodToDraft')
        ->assertSet('mealItems', [
            ['food_id' => $foodA->id, 'weight' => 125.5],
            ['food_id' => $foodB->id, 'weight' => 50.0],
        ])
        ->assertSee('2 de 10 alimentos');
});

it('removes a specific food from the draft while preserving the other food order and editor state', function () {
    App::setLocale('pt_BR');

    $foodA = Food::factory()->create();
    $foodB = Food::factory()->create();
    $foodC = Food::factory()->create();

    $mealItems = [
        ['food_id' => $foodA->id, 'weight' => 100.0],
        ['food_id' => $foodB->id, 'weight' => 150.0],
        ['food_id' => $foodC->id, 'weight' => 200.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->set('mealName', 'Lunch')
        ->call('removeMealItem', $foodB->id)
        ->assertSet('mealItems', [
            ['food_id' => $foodA->id, 'weight' => 100.0],
            ['food_id' => $foodC->id, 'weight' => 200.0],
        ])
        ->assertSet('mealName', 'Lunch')
        ->assertSet('isMealEditorOpen', true)
        ->assertSet('isFoodModalOpen', false)
        ->assertSee('2 de 10 alimentos');
});

it('returns the meal editor to its items empty state after removing its last food', function () {
    App::setLocale('pt_BR');

    $food = Food::factory()->create();

    $mealItems = [
        ['food_id' => $food->id, 'weight' => 100.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('removeMealItem', $food->id)
        ->assertSet('mealItems', [])
        ->assertDontSeeHtml('data-testid="meal-items-list"')
        ->assertSeeHtml('data-testid="meal-items-empty-state"')
        ->assertSee('0 de 10 alimentos');
});

it('leaves the draft and editor state unchanged when removing a food that is not in the draft', function () {
    $draftFood = Food::factory()->create();
    $selectedFood = Food::factory()->create();
    $missingFood = Food::factory()->create();

    $mealItems = [
        ['food_id' => $draftFood->id, 'weight' => 125.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->set('mealName', 'Lunch')
        ->call('openFoodModal')
        ->set('foodSearch', 'Pending search')
        ->call('selectFood', $selectedFood->id)
        ->set('foodWeight', '250')
        ->call('removeMealItem', $missingFood->id)
        ->assertSet('mealItems', [
            ['food_id' => $draftFood->id, 'weight' => 125.0],
        ])
        ->assertSet('mealName', 'Lunch')
        ->assertSet('isMealEditorOpen', true)
        ->assertSet('isFoodModalOpen', true)
        ->assertSet('foodSearch', 'Pending search')
        ->assertSet('selectedFoodId', $selectedFood->id)
        ->assertSet('foodWeight', '250');
});

it('renders each meal item remove control bound to its food id', function () {
    $food = Food::factory()->create();

    $mealItems = [
        ['food_id' => $food->id, 'weight' => 100.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->assertSeeHtml('data-testid="remove-meal-item"')
        ->assertSeeHtml('wire:click="removeMealItem('.$food->id.')"');
});

it('renders each meal item edit control bound to its food id', function () {
    $food = Food::factory()->create();

    $mealItems = [
        ['food_id' => $food->id, 'weight' => 100.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->assertSeeHtml('data-testid="edit-meal-item"')
        ->assertSeeHtml('wire:click="editMealItem('.$food->id.')"');
});

it('opens the food modal to edit a draft item with its current food and numeric weight', function (float $weight, string $expectedWeight) {
    $food = Food::factory()->create();

    $mealItems = [
        ['food_id' => $food->id, 'weight' => $weight],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->set('mealName', 'Lunch')
        ->set('foodSearch', 'Pending search')
        ->call('editMealItem', $food->id)
        ->assertSet('editingMealItemFoodId', $food->id)
        ->assertSet('selectedFoodId', $food->id)
        ->assertSet('foodWeight', $expectedWeight)
        ->assertSet('foodSearch', '')
        ->assertSet('isFoodModalOpen', true)
        ->assertSet('mealName', 'Lunch')
        ->assertSet('mealItems', [
            ['food_id' => $food->id, 'weight' => $weight],
        ])
        ->assertSeeHtml('data-testid="update-food-in-draft-enabled"')
        ->assertSeeHtml('wire:click="updateFoodInDraft"')
        ->assertSee(__('ui.meals.save'));
})->with([
    'whole number weight' => [100.0, '100'],
    'decimal weight' => [12.5, '12.5'],
]);

it('opens the food modal to edit an item at the meal item limit', function () {
    $mealItems = Food::factory()->count(10)->create()
        ->map(fn (Food $food) => ['food_id' => $food->id, 'weight' => 100.0])
        ->all();

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->assertSeeHtml('wire:click="editMealItem('.$mealItems[0]['food_id'].')"')
        ->call('editMealItem', $mealItems[0]['food_id'])
        ->assertSet('editingMealItemFoodId', $mealItems[0]['food_id'])
        ->assertSet('isFoodModalOpen', true);
});

it('does not open or alter the editor state when editing a food absent from the draft', function () {
    $draftFood = Food::factory()->create();
    $missingFood = Food::factory()->create();

    $mealItems = [
        ['food_id' => $draftFood->id, 'weight' => 100.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->set('mealName', 'Lunch')
        ->call('editMealItem', $missingFood->id)
        ->assertSet('editingMealItemFoodId', null)
        ->assertSet('isFoodModalOpen', false)
        ->assertSet('selectedFoodId', null)
        ->assertSet('foodWeight', '')
        ->assertSet('foodSearch', '')
        ->assertSet('mealName', 'Lunch')
        ->assertSet('mealItems', [
            ['food_id' => $draftFood->id, 'weight' => 100.0],
        ]);
});

it('updates a draft food weight in place and clears the food modal edit state after success', function () {
    $foodA = Food::factory()->create();
    $foodB = Food::factory()->create();

    $mealItems = [
        ['food_id' => $foodA->id, 'weight' => 100.0],
        ['food_id' => $foodB->id, 'weight' => 150.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('editMealItem', $foodA->id)
        ->set('foodWeight', '250')
        ->call('updateFoodInDraft')
        ->assertSet('mealItems', [
            ['food_id' => $foodA->id, 'weight' => 250.0],
            ['food_id' => $foodB->id, 'weight' => 150.0],
        ])
        ->assertSet('editingMealItemFoodId', null)
        ->assertSet('isFoodModalOpen', false)
        ->assertSet('selectedFoodId', null)
        ->assertSet('foodWeight', '')
        ->assertSet('foodSearch', '');
});

it('allows replacing an edited food weight exactly at the maximum without adding its previous weight', function () {
    $food = Food::factory()->create();

    $mealItems = [
        ['food_id' => $food->id, 'weight' => 100.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('editMealItem', $food->id)
        ->set('foodWeight', '10000')
        ->assertSeeHtml('data-testid="update-food-in-draft-enabled"')
        ->assertSeeHtml('wire:click="updateFoodInDraft"')
        ->call('updateFoodInDraft')
        ->assertSet('mealItems', [
            ['food_id' => $food->id, 'weight' => 10000.0],
        ]);
});

it('replaces an edited draft food with a new food while preserving its position', function () {
    $foodA = Food::factory()->create();
    $foodB = Food::factory()->create();
    $foodC = Food::factory()->create();

    $mealItems = [
        ['food_id' => $foodA->id, 'weight' => 100.0],
        ['food_id' => $foodB->id, 'weight' => 150.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('editMealItem', $foodA->id)
        ->call('selectFood', $foodC->id)
        ->set('foodWeight', '200')
        ->call('updateFoodInDraft')
        ->assertSet('mealItems', [
            ['food_id' => $foodC->id, 'weight' => 200.0],
            ['food_id' => $foodB->id, 'weight' => 150.0],
        ]);
});

it('replaces an edited food with a new food at the meal item limit', function () {
    $foods = Food::factory()->count(11)->create();
    $mealItems = $foods->take(10)
        ->map(fn (Food $food) => ['food_id' => $food->id, 'weight' => 100.0])
        ->all();
    $replacementFood = $foods->last();
    $expectedMealItems = $mealItems;
    $expectedMealItems[0] = ['food_id' => $replacementFood->id, 'weight' => 200.0];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('editMealItem', $mealItems[0]['food_id'])
        ->call('selectFood', $replacementFood->id)
        ->set('foodWeight', '200')
        ->call('updateFoodInDraft')
        ->assertSet('mealItems', $expectedMealItems);
});

it('merges an edited draft food into an existing destination food while preserving destination order', function () {
    $foodA = Food::factory()->create();
    $foodB = Food::factory()->create();
    $foodC = Food::factory()->create();

    $mealItems = [
        ['food_id' => $foodA->id, 'weight' => 100.0],
        ['food_id' => $foodB->id, 'weight' => 300.0],
        ['food_id' => $foodC->id, 'weight' => 150.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('editMealItem', $foodA->id)
        ->call('selectFood', $foodB->id)
        ->set('foodWeight', '200')
        ->call('updateFoodInDraft')
        ->assertSet('mealItems', [
            ['food_id' => $foodB->id, 'weight' => 500.0],
            ['food_id' => $foodC->id, 'weight' => 150.0],
        ]);
});

it('allows an edit merge that reaches the combined food weight maximum exactly', function () {
    $foodA = Food::factory()->create();
    $foodB = Food::factory()->create();

    $mealItems = [
        ['food_id' => $foodA->id, 'weight' => 100.0],
        ['food_id' => $foodB->id, 'weight' => 9000.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('editMealItem', $foodA->id)
        ->call('selectFood', $foodB->id)
        ->set('foodWeight', '1000')
        ->assertSeeHtml('data-testid="update-food-in-draft-enabled"')
        ->assertSeeHtml('wire:click="updateFoodInDraft"')
        ->call('updateFoodInDraft')
        ->assertSet('mealItems', [
            ['food_id' => $foodB->id, 'weight' => 10000.0],
        ]);
});

it('keeps the edit state intact when an edit merge exceeds the combined food weight maximum', function () {
    App::setLocale('pt_BR');

    $foodA = Food::factory()->create();
    $foodB = Food::factory()->create([
        'name_pt' => 'Feijão',
        'name_en' => 'Beans',
    ]);

    $mealItems = [
        ['food_id' => $foodA->id, 'weight' => 100.0],
        ['food_id' => $foodB->id, 'weight' => 9000.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('editMealItem', $foodA->id)
        ->call('selectFood', $foodB->id)
        ->set('foodWeight', '1001')
        ->assertSeeHtml('data-testid="update-food-in-draft-disabled"')
        ->assertSeeHtml('disabled')
        ->assertDontSeeHtml('wire:click="updateFoodInDraft"')
        ->assertSee(__('ui.meals.total_quantity_too_high', [
            'food' => 'Feijão',
            'max' => '10.000',
        ]))
        ->call('updateFoodInDraft')
        ->assertSet('mealItems', [
            ['food_id' => $foodA->id, 'weight' => 100.0],
            ['food_id' => $foodB->id, 'weight' => 9000.0],
        ])
        ->assertSet('editingMealItemFoodId', $foodA->id)
        ->assertSet('isFoodModalOpen', true)
        ->assertSet('selectedFoodId', $foodB->id)
        ->assertSet('foodWeight', '1001');
});

it('cancels an item edit without changing the draft', function () {
    $food = Food::factory()->create();

    $mealItems = [
        ['food_id' => $food->id, 'weight' => 100.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('editMealItem', $food->id)
        ->call('cancelFoodModal')
        ->assertSet('mealItems', [
            ['food_id' => $food->id, 'weight' => 100.0],
        ])
        ->assertSet('editingMealItemFoodId', null)
        ->assertSet('isFoodModalOpen', false)
        ->assertSet('selectedFoodId', null)
        ->assertSet('foodWeight', '')
        ->assertSet('foodSearch', '');
});

it('keeps the temporary edit state intact when its original draft food no longer exists', function () {
    $originalFood = Food::factory()->create();
    $selectedFood = Food::factory()->create();

    $mealItems = [
        ['food_id' => $selectedFood->id, 'weight' => 150.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->set('editingMealItemFoodId', $originalFood->id)
        ->set('isFoodModalOpen', true)
        ->set('foodSearch', 'Pending search')
        ->set('selectedFoodId', $selectedFood->id)
        ->set('foodWeight', '200')
        ->call('updateFoodInDraft')
        ->assertSet('mealItems', [
            ['food_id' => $selectedFood->id, 'weight' => 150.0],
        ])
        ->assertSet('editingMealItemFoodId', $originalFood->id)
        ->assertSet('isFoodModalOpen', true)
        ->assertSet('foodSearch', 'Pending search')
        ->assertSet('selectedFoodId', $selectedFood->id)
        ->assertSet('foodWeight', '200');
});

it('does not update the draft when the edited food weight is invalid', function () {
    $food = Food::factory()->create();

    $mealItems = [
        ['food_id' => $food->id, 'weight' => 100.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('editMealItem', $food->id)
        ->set('foodWeight', '0')
        ->call('updateFoodInDraft')
        ->assertSet('mealItems', [
            ['food_id' => $food->id, 'weight' => 100.0],
        ])
        ->assertSet('editingMealItemFoodId', $food->id)
        ->assertSet('isFoodModalOpen', true)
        ->assertSet('selectedFoodId', $food->id)
        ->assertSet('foodWeight', '0');
});

it('allows a duplicate food total exactly at the maximum weight', function () {
    $food = Food::factory()->create();

    $mealItems = [
        ['food_id' => $food->id, 'weight' => 8000.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->set('foodWeight', '2000')
        ->assertSeeHtml('data-testid="add-food-to-draft-enabled"')
        ->assertSeeHtml('wire:click="addFoodToDraft"')
        ->call('addFoodToDraft')
        ->assertSet('mealItems', [
            ['food_id' => $food->id, 'weight' => 10000.0],
        ]);
});

it('disables addition above the combined food weight limit and shows the localized total limit message', function (string $locale, string $message) {
    App::setLocale($locale);

    $food = Food::factory()->create([
        'name_pt' => 'Arroz integral',
        'name_en' => 'Brown rice',
    ]);

    $mealItems = [
        ['food_id' => $food->id, 'weight' => 8000.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->set('foodWeight', '2001')
        ->assertSeeHtml('data-testid="add-food-to-draft-disabled"')
        ->assertSeeHtml('disabled')
        ->assertDontSeeHtml('wire:click="addFoodToDraft"')
        ->assertSee($message);
})->with([
    'Brazilian Portuguese' => [
        'pt_BR',
        'A quantidade total de Arroz integral na refeição não pode ultrapassar 10.000 g.',
    ],
    'English' => [
        'en',
        'The total amount of Brown rice in the meal cannot exceed 10,000 g.',
    ],
]);

it('keeps the draft and food modal state intact when add food is called directly above the combined weight limit', function () {
    $food = Food::factory()->create();

    $mealItems = [
        ['food_id' => $food->id, 'weight' => 8000.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->set('mealName', 'Lunch')
        ->call('openFoodModal')
        ->set('foodSearch', 'Pending search')
        ->call('selectFood', $food->id)
        ->set('foodWeight', '2001')
        ->call('addFoodToDraft')
        ->assertSet('mealItems', [
            ['food_id' => $food->id, 'weight' => 8000.0],
        ])
        ->assertSet('isFoodModalOpen', true)
        ->assertSet('mealName', 'Lunch')
        ->assertSet('foodSearch', 'Pending search')
        ->assertSet('selectedFoodId', $food->id)
        ->assertSet('foodWeight', '2001');
});

it('does not add a food to the meal draft when the weight is invalid', function () {
    $food = Food::factory()->create();

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->set('foodWeight', '0')
        ->call('addFoodToDraft')
        ->assertSet('mealItems', [])
        ->assertSet('isFoodModalOpen', true);
});

it('does not add an unavailable selected food when add food is called directly', function () {
    $draftFood = Food::factory()->create();
    $unavailableFood = Food::factory()->create();
    $unavailableFoodId = $unavailableFood->id;
    $unavailableFood->delete();

    $mealItems = [
        ['food_id' => $draftFood->id, 'weight' => 100.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->call('openFoodModal')
        ->set('foodSearch', 'Pending search')
        ->set('selectedFoodId', $unavailableFoodId)
        ->set('foodWeight', '250')
        ->call('addFoodToDraft')
        ->assertSet('mealItems', [
            ['food_id' => $draftFood->id, 'weight' => 100.0],
        ])
        ->assertSet('isFoodModalOpen', true)
        ->assertSet('foodSearch', 'Pending search')
        ->assertSet('selectedFoodId', $unavailableFoodId)
        ->assertSet('foodWeight', '250');
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

it('calculates the nutrition preview from a comma decimal weight', function () {
    App::setLocale('pt_BR');

    $food = Food::factory()->create([
        'calories_per_100g' => 100,
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->set('foodWeight', '12,5')
        ->assertSet('foodWeight', '12,5')
        ->assertSee('12,5 kcal')
        ->assertDontSee(__('ui.meals.quantity_must_be_numeric'));
});

it('does not show the nutrition preview or a weight error while the selected food weight is empty', function () {
    $food = Food::factory()->create();

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->set('foodWeight', '')
        ->assertDontSeeHtml('data-testid="selected-food-nutrition-preview"')
        ->assertDontSee(__('ui.meals.quantity_must_be_numeric'))
        ->assertDontSee(__('ui.meals.quantity_must_be_positive'))
        ->assertDontSee(__('ui.meals.quantity_too_high', ['max' => '10.000']));
});

it('does not show the nutrition preview and shows a friendly message when the selected food weight is zero', function () {
    $food = Food::factory()->create();

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->set('foodWeight', '0')
        ->assertDontSeeHtml('data-testid="selected-food-nutrition-preview"')
        ->assertSee(__('ui.meals.quantity_must_be_positive'));
});

it('does not show the nutrition preview when the selected food weight is not numeric', function () {
    $food = Food::factory()->create();

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->set('foodWeight', 'invalid')
        ->assertDontSeeHtml('data-testid="selected-food-nutrition-preview"')
        ->assertSee(__('ui.meals.quantity_must_be_numeric'));
});

it('shows the nutrition preview when the selected food weight is exactly the maximum', function () {
    $food = Food::factory()->create([
        'calories_per_100g' => 100,
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->set('foodWeight', (string) FoodWeightInputService::MAXIMUM_IN_GRAMS)
        ->assertSeeHtml('data-testid="selected-food-nutrition-preview"')
        ->assertDontSee(__('ui.meals.quantity_too_high', ['max' => '10.000']));
});

it('does not show the nutrition preview when the selected food weight exceeds the maximum', function () {
    $food = Food::factory()->create();

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->set('foodWeight', (string) (FoodWeightInputService::MAXIMUM_IN_GRAMS + 1))
        ->assertDontSeeHtml('data-testid="selected-food-nutrition-preview"')
        ->assertSee(__('ui.meals.quantity_too_high', ['max' => '10.000']));
});

it('shows the localized meal weight validation messages', function (string $locale, string $invalidMessage, string $zeroMessage, string $maximumMessage) {
    App::setLocale($locale);

    $food = Food::factory()->create();

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->set('foodWeight', 'invalid')
        ->assertSee($invalidMessage)

        ->set('foodWeight', '0')
        ->assertSee($zeroMessage)

        ->set('foodWeight', (string) (FoodWeightInputService::MAXIMUM_IN_GRAMS + 1))
        ->assertSee($maximumMessage);
})->with([
    'Brazilian Portuguese' => [
        'pt_BR',
        'Informe uma quantidade válida em gramas.',
        'Informe uma quantidade maior que zero.',
        'Informe uma quantidade de até 10.000 g.',
    ],
    'English' => [
        'en',
        'Enter a valid amount in grams.',
        'Enter an amount greater than zero.',
        'Enter an amount of up to 10,000 g.',
    ],
]);

it('formats the selected food nutrition preview summary and normal values in pt-BR', function () {
    App::setLocale('pt_BR');

    $food = Food::factory()->create([
        'calories_per_100g' => 1234.50,
        'protein_per_100g' => 20.00,
        'carbs_per_100g' => 100.00,
        'fat_per_100g' => 0.00,
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->set('foodWeight', '10.5')
        ->assertSee('Para 10,5 g')
        ->assertSee('129,62 kcal')
        ->assertSee('2,1 g')
        ->assertSee('10,5 g')
        ->assertSee('0 g');
});

it('shows a less than value for selected food nutrition preview values below the display minimum', function () {
    App::setLocale('pt_BR');

    $food = Food::factory()->create([
        'calories_per_100g' => 0.50,
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id)
        ->set('foodWeight', '1')
        ->assertSee('< 0,01 kcal')
        ->assertDontSee('0 kcal');
});

it('shows the localized selected food details copy', function (string $locale, string $foodName, string $amount, string $per100Grams, string $protein, string $carbs, string $fat) {
    App::setLocale($locale);

    $food = Food::factory()->create([
        'name_pt' => 'Alimento nutricional',
        'name_en' => 'Nutritional food',
    ]);

    $component = Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->call('selectFood', $food->id);

    $component
        ->assertSee($foodName)
        ->assertSee($amount)
        ->assertSee($per100Grams)
        ->assertSee($protein)
        ->assertSee($carbs)
        ->assertSee($fat);
})->with([
    'Brazilian Portuguese' => [
        'pt_BR',
        'Alimento nutricional',
        'Quantidade',
        'Para 100 g',
        'Proteínas',
        'Carboidratos',
        'Gorduras',
    ],
    'English' => [
        'en',
        'Nutritional food',
        'Amount',
        'For 100 g',
        'Protein',
        'Carbs',
        'Fat',
    ],
]);

it('shows the localized selected food badge', function (string $locale, string $badge) {
    App::setLocale($locale);

    $banana = Food::factory()->create([
        'name_pt' => 'Banana',
        'name_en' => 'Banana',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->set('foodSearch', 'B')
        ->call('selectFood', $banana->id)
        ->assertSee($badge);
})->with([
    'Brazilian Portuguese' => ['pt_BR', 'Selecionado'],
    'English' => ['en', 'Selected'],
]);

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

it('renders the localized food search empty state', function (string $locale, string $heading, string $description) {
    App::setLocale($locale);

    Food::factory()->create([
        'name_pt' => 'Banana',
        'name_en' => 'Banana',
    ]);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->call('openFoodModal')
        ->set('foodSearch', 'X')
        ->assertSee($heading)
        ->assertSee($description);
})->with([
    'Brazilian Portuguese' => ['pt_BR', 'Nenhum alimento encontrado', 'Tente buscar por outro nome ou termo.'],
    'English' => ['en', 'No foods found', 'Try searching for another name or term.'],
]);

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
    $food = Food::factory()->create();
    $mealItems = [
        ['food_id' => $food->id, 'weight' => 100.0],
    ];

    $component = mountMealEditorWithDraft($mealItems);

    $component
        ->assertNotSet('mealItems', [])
        ->call('cancelMealEditor')
        ->assertSet('isMealEditorOpen', false)
        ->assertSet('editingMealId', null)
        ->assertSet('mealName', '')
        ->assertSet('mealItems', []);
});

it('renders the localized empty state and new meal editor', function (string $locale, string $emptyStateHeading, string $emptyStateDescription, string $createMeal, string $newMeal) {
    App::setLocale($locale);

    $component = Livewire::test(Meals::class);

    $component
        ->assertSee($emptyStateHeading)
        ->assertSee($emptyStateDescription)
        ->assertSee($createMeal);

    $component
        ->call('createMeal')
        ->assertSee($newMeal)
        ->assertSee($createMeal)
        ->assertSeeHtml('data-testid="meal-editor"')
        ->assertSeeHtml('data-testid="meal-name"')
        ->assertSeeHtml('data-testid="submit-meal"');
})->with([
    'Brazilian Portuguese' => [
        'pt_BR',
        'Nenhuma refeição criada ainda.',
        'Crie uma refeição para começar a organizar seus alimentos e acompanhar os totais nutricionais.',
        'Criar refeição',
        'Nova refeição',
    ],
    'English' => [
        'en',
        'No meals created yet.',
        'Create a meal to start organizing your foods and tracking nutritional totals.',
        'Create meal',
        'New meal',
    ],
]);

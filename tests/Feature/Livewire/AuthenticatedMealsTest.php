<?php

use App\Livewire\Meals;
use App\Models\Food;
use App\Models\Meal;
use App\Models\MealItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function authenticatedMealsUser(): User
{
    return User::query()->create([
        'name' => 'MESA User',
        'email' => 'user@example.com',
        'password' => 'password',
    ]);
}

function storedMeal(User $user, Food $food, string $name = 'Lunch', float $weight = 100.0): Meal
{
    $meal = $user->meals()->create(['name' => $name]);

    $meal->items()->create([
        'food_id' => $food->id,
        'weight' => $weight,
    ]);

    return $meal;
}

it('relates users, meals, meal items, and foods', function () {
    $user = authenticatedMealsUser();
    $food = Food::factory()->create();
    $meal = storedMeal($user, $food, 'Lunch', 12.5);
    $item = $meal->items()->sole();

    expect($user->meals()->sole()->is($meal))->toBeTrue();
    expect($meal->user->is($user))->toBeTrue();
    expect($meal->items->sole()->is($item))->toBeTrue();
    expect($item->meal->is($meal))->toBeTrue();
    expect($item->food->is($food))->toBeTrue();
    expect($food->mealItems->sole()->is($item))->toBeTrue();
});

it('persists an authenticated meal and its items without using the guest session', function () {
    $user = authenticatedMealsUser();
    $foodA = Food::factory()->create();
    $foodB = Food::factory()->create();

    Livewire::actingAs($user);

    Livewire::test(Meals::class)
        ->call('createMeal')
        ->set('mealName', '  Lunch  ')
        ->call('openFoodModal')
        ->call('selectFood', $foodA->id)
        ->set('foodWeight', '100')
        ->call('addFoodToDraft')
        ->call('openFoodModal')
        ->call('selectFood', $foodB->id)
        ->set('foodWeight', '50.5')
        ->call('addFoodToDraft')
        ->call('submitMeal')
        ->assertSet('isMealEditorOpen', false)
        ->assertDontSeeHtml('data-testid="meal-auth-callout"');

    $meal = Meal::query()->with('items')->sole();

    expect($meal->user_id)->toBe($user->id);
    expect($meal->name)->toBe('Lunch');
    expect($meal->items->pluck('food_id')->all())->toBe([$foodA->id, $foodB->id]);
    expect($meal->items->pluck('weight')->all())->toBe([100.0, 50.5]);
    expect(session()->get('meals'))->toBeNull();

    Livewire::test(Meals::class)
        ->assertSeeHtml('data-testid="meal-list-item"')
        ->assertSeeHtml('data-meal-id="'.$meal->id.'"');
});

it('updates an authenticated meal and replaces its items', function () {
    $user = authenticatedMealsUser();
    $foodA = Food::factory()->create();
    $foodB = Food::factory()->create();
    $meal = storedMeal($user, $foodA, 'Lunch', 100.0);

    Livewire::actingAs($user);

    Livewire::test(Meals::class)
        ->call('editMeal', $meal->id)
        ->set('mealName', 'Updated lunch')
        ->call('removeMealItem', $foodA->id)
        ->call('openFoodModal')
        ->call('selectFood', $foodB->id)
        ->set('foodWeight', '200')
        ->call('addFoodToDraft')
        ->call('submitMeal')
        ->assertSet('isMealEditorOpen', false);

    $meal->refresh();

    expect($meal->name)->toBe('Updated lunch');
    expect($meal->items()->pluck('food_id')->all())->toBe([$foodB->id]);
    expect($meal->items()->pluck('weight')->all())->toBe([200.0]);
    expect(session()->get('meals'))->toBeNull();
});

it('deletes an authenticated meal and its items', function () {
    $user = authenticatedMealsUser();
    $food = Food::factory()->create();
    $meal = storedMeal($user, $food);

    Livewire::actingAs($user);

    Livewire::test(Meals::class)
        ->call('deleteMeal', $meal->id);

    expect(Meal::query()->find($meal->id))->toBeNull();
    expect(MealItem::query()->count())->toBe(0);
});

it('does not list meals owned by another user', function () {
    $owner = authenticatedMealsUser();
    $viewer = User::query()->create([
        'name' => 'Another User',
        'email' => 'another@example.com',
        'password' => 'password',
    ]);
    $food = Food::factory()->create();
    $meal = storedMeal($owner, $food);

    Livewire::actingAs($viewer);

    Livewire::test(Meals::class)
        ->assertDontSeeHtml('data-meal-id="'.$meal->id.'"');
});

it('does not list guest session meals for an authenticated user', function () {
    $user = authenticatedMealsUser();
    $food = Food::factory()->create();

    session()->put('meals', [
        [
            'id' => 1,
            'name' => 'Guest meal',
            'items' => [
                ['food_id' => $food->id, 'weight' => 100.0],
            ],
        ],
    ]);

    Livewire::actingAs($user);

    Livewire::test(Meals::class)
        ->assertDontSeeHtml('data-testid="meal-list"')
        ->assertDontSeeHtml('data-meal-id="1"');
});

it('does not load a meal owned by another user for editing', function () {
    $owner = authenticatedMealsUser();
    $viewer = User::query()->create([
        'name' => 'Another User',
        'email' => 'another@example.com',
        'password' => 'password',
    ]);
    $food = Food::factory()->create();
    $meal = storedMeal($owner, $food);

    Livewire::actingAs($viewer);

    Livewire::test(Meals::class)
        ->call('editMeal', $meal->id)
        ->assertSet('editingMealId', null)
        ->assertSet('isMealEditorOpen', false)
        ->assertSet('mealItems', []);

    expect($meal->fresh()->name)->toBe('Lunch');
    expect($meal->items()->count())->toBe(1);
});

it('does not delete a meal owned by another user', function () {
    $owner = authenticatedMealsUser();
    $viewer = User::query()->create([
        'name' => 'Another User',
        'email' => 'another@example.com',
        'password' => 'password',
    ]);
    $food = Food::factory()->create();
    $meal = storedMeal($owner, $food);

    Livewire::actingAs($viewer);

    Livewire::test(Meals::class)
        ->call('deleteMeal', $meal->id);

    expect(Meal::query()->find($meal->id)->is($meal))->toBeTrue();
    expect($meal->items()->count())->toBe(1);
});

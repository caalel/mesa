<?php

use App\Models\Food;
use App\Models\Meal;
use App\Models\User;
use App\Services\GuestMealsMigrator;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function guestMealsMigrationUser(): User
{
    return User::query()->create([
        'name' => 'MESA User',
        'email' => 'user@example.com',
        'password' => 'password',
    ]);
}

it('migrates all guest meals without deduplicating or reusing guest identities', function () {
    $user = guestMealsMigrationUser();
    $foodA = Food::factory()->create();
    $foodB = Food::factory()->create();
    $accountMeal = $user->meals()->create(['name' => 'Lunch']);
    $accountMeal->items()->create([
        'food_id' => $foodA->id,
        'weight' => 100.0,
    ]);
    $guestMeals = [
        [
            'id' => $accountMeal->id,
            'name' => 'Lunch',
            'items' => [
                ['food_id' => $foodA->id, 'weight' => 100.0],
            ],
        ],
        [
            'id' => 99,
            'name' => 'Dinner',
            'items' => [
                ['food_id' => $foodA->id, 'weight' => 125.5],
                ['food_id' => $foodB->id, 'weight' => 50.0],
            ],
        ],
    ];

    session()->put('meals', $guestMeals);
    session()->put('meal_auth_callout_handled', true);

    app(GuestMealsMigrator::class)->migrate($user);

    $meals = $user->meals()->with('items')->orderBy('id')->get();
    $migratedLunch = $meals->first(fn (Meal $meal) => $meal->id !== $accountMeal->id && $meal->name === 'Lunch');
    $dinner = $meals->firstWhere('name', 'Dinner');

    expect($meals)->toHaveCount(3);
    expect($meals->where('name', 'Lunch'))->toHaveCount(2);
    expect($migratedLunch->items->pluck('food_id')->all())->toBe([$foodA->id]);
    expect($migratedLunch->items->pluck('weight')->all())->toBe([100.0]);
    expect($dinner->items->pluck('food_id')->all())->toBe([$foodA->id, $foodB->id]);
    expect($dinner->items->pluck('weight')->all())->toBe([125.5, 50.0]);
    expect(session()->has('meals'))->toBeFalse();
    expect(session()->get('meal_auth_callout_handled'))->toBeTrue();
});

it('rolls back guest meal migration and preserves the session when an item cannot be persisted', function () {
    $user = guestMealsMigrationUser();
    $food = Food::factory()->create();
    $guestMeals = [
        [
            'id' => 1,
            'name' => 'Valid meal',
            'items' => [
                ['food_id' => $food->id, 'weight' => 100.0],
            ],
        ],
        [
            'id' => 2,
            'name' => 'Invalid meal',
            'items' => [
                ['food_id' => 999999, 'weight' => 100.0],
            ],
        ],
    ];

    session()->put('meals', $guestMeals);

    expect(fn () => app(GuestMealsMigrator::class)->migrate($user))
        ->toThrow(QueryException::class);

    expect(Meal::query()->count())->toBe(0);
    expect(session()->get('meals'))->toEqual($guestMeals);
});

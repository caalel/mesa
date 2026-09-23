<?php

use App\Models\User;
use App\Models\Food;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('logs an authenticated user out through Fortify', function () {
    $user = User::query()->create([
        'name' => 'Ana Silva',
        'email' => 'ana@example.com',
        'password' => Hash::make('correct-horse-battery-staple'),
    ]);
    $food = Food::factory()->create();
    $meal = $user->meals()->create(['name' => 'Account meal']);
    $meal->items()->create([
        'food_id' => $food->id,
        'weight' => 100.0,
    ]);

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/')
        ->assertSessionMissing('meals');

    $this->assertGuest();
    $this->assertDatabaseHas('meals', ['id' => $meal->id]);
});

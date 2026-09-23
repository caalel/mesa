<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class GuestMealsMigrator
{
    public function migrate(User $user): void
    {
        $guestMeals = $this->guestMeals();

        if ($guestMeals === []) {
            return;
        }

        DB::transaction(function () use ($user, $guestMeals): void {
            $this->persist($user, $guestMeals);
        });

        $this->forgetGuestMeals();
    }

    public function migrateWithinTransaction(User $user): void
    {
        $guestMeals = $this->guestMeals();

        if ($guestMeals === []) {
            return;
        }

        $this->persist($user, $guestMeals);
    }

    public function forgetGuestMeals(): void
    {
        session()->forget('meals');
    }

    private function guestMeals(): array
    {
        return session()->get('meals', []);
    }

    private function persist(User $user, array $guestMeals): void
    {
        foreach ($guestMeals as $guestMeal) {
            $meal = $user->meals()->create([
                'name' => $guestMeal['name'],
            ]);

            $meal->items()->createMany($guestMeal['items']);
        }
    }
}

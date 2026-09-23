<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\GuestMealsMigrator;
use Illuminate\Auth\Events\Login;
use Throwable;

class MigrateGuestMealsOnLogin
{
    public function __construct(private GuestMealsMigrator $guestMealsMigrator,) {}

    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        try {
            $this->guestMealsMigrator->migrate($event->user);
        } catch (Throwable $exception) {
            auth()->guard($event->guard)->logout();

            throw $exception;
        }
    }
}

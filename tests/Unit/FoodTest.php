<?php

use App\Models\Food;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

uses(TestCase::class);

it('returns the Portuguese name for the pt_BR locale', function () {
    $food = new Food();
    $food->setAttribute('name_pt', 'Arroz integral');
    $food->setAttribute('name_en', 'Brown rice');

    App::setLocale('pt_BR');

    expect($food->localized_name)->toBe('Arroz integral');
});

it('returns the English name for the en locale', function () {
    $food = new Food();
    $food->setAttribute('name_pt', 'Arroz integral');
    $food->setAttribute('name_en', 'Brown rice');

    App::setLocale('en');

    expect($food->localized_name)->toBe('Brown rice');
});

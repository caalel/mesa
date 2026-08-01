<?php

use App\Models\Food;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists a food source identity', function () {
    $identity = [
        'data_source' => 'taco',
        'source_code' => '1',
        'source_version' => '4',
    ];

    $food = Food::factory()->create($identity);

    expect($food->data_source)->toBe('taco')
        ->and($food->source_code)->toBe('1')
        ->and($food->source_version)->toBe('4');
});

it('prevents duplicate food source identities', function () {
    $identity = [
        'data_source' => 'taco',
        'source_code' => '1',
        'source_version' => '4',
    ];

    Food::factory()->create($identity);

    expect(fn () => Food::factory()->create($identity))
        ->toThrow(QueryException::class);
});

it('allows the same source code from different data sources', function () {
    Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '1',
        'source_version' => '4',
    ]);

    Food::factory()->create([
        'data_source' => 'usda',
        'source_code' => '1',
        'source_version' => '4',
    ]);

    expect(Food::count())->toBe(2);
});

it('allows the same source code from different source versions', function () {
    Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '1',
        'source_version' => '4',
    ]);

    Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '1',
        'source_version' => '5',
    ]);

    expect(Food::count())->toBe(2);
});

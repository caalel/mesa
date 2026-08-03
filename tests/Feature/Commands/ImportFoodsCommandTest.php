<?php

use App\Models\Food;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

afterEach(function () {
    foreach (glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'foods-import-command-*') ?: [] as $path) {
        @unlink($path);
    }
});

/*
|--------------------------------------------------------------------------
| Tests
|--------------------------------------------------------------------------
*/

it('does not persist foods during a dry run', function () {
    $csvPath = tempnam(sys_get_temp_dir(), 'foods-import-command-');

    if ($csvPath === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    File::put($csvPath, <<<CSV
        source_code,name_pt,name_en,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g
        001,Leite desnatado,Skim milk,34,3.37,4.96,0.08
        002,Leite integral,Whole milk,61,3.15,4.80,3.25
        CSV);

    $this->artisan('foods:import', [
        '--dry-run' => true,
        '--path' => $csvPath,
    ])
        ->expectsOutputToContain('Valid rows: 2')
        ->expectsOutputToContain('Invalid rows: 0')
        ->assertSuccessful();

    expect(Food::query()->count())->toBe(0);
});

it('persists foods during a normal import', function () {
    $csvPath = tempnam(sys_get_temp_dir(), 'foods-import-command-');

    if ($csvPath === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    File::put($csvPath, <<<CSV
        source_code,name_pt,name_en,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g
        001,Leite desnatado,Skim milk,34,3.37,4.96,0.08
        002,Leite integral,Whole milk,61,3.15,4.80,3.25
        CSV);

    $this->artisan('foods:import', [
        '--path' => $csvPath,
    ])
        ->expectsOutputToContain('Valid rows: 2')
        ->expectsOutputToContain('Invalid rows: 0')
        ->assertSuccessful();

    expect(Food::query()->count())->toBe(2);
});

it('displays invalid rows while persisting valid foods', function () {
    $csvPath = tempnam(sys_get_temp_dir(), 'foods-import-command-');

    if ($csvPath === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    File::put($csvPath, <<<CSV
        source_code,name_pt,name_en,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g
        001,Leite desnatado,Skim milk,34,3.37,4.96,0.08
        002,Nutriente invalido,Invalid nutrient,invalid,1,2,3
        CSV);

    $this->artisan('foods:import', [
        '--path' => $csvPath,
    ])
        ->expectsOutputToContain('Valid rows: 1')
        ->expectsOutputToContain('Invalid rows: 1')
        ->expectsOutputToContain('Line 3: calories_per_100g')
        ->assertSuccessful();

    expect(Food::query()->count())->toBe(1);
});

it('fails when the CSV header is invalid', function () {
    $csvPath = tempnam(sys_get_temp_dir(), 'foods-import-command-');

    if ($csvPath === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    File::put($csvPath, <<<CSV
        source_code,name_pt,name_en,calories_per_100g,protein_per_100g,carbs_per_100g
        001,Leite desnatado,,34,3.37,4.96
        CSV);

    $this->artisan('foods:import', [
        '--path' => $csvPath,
    ])
        ->expectsOutputToContain('fat_per_100g')
        ->assertFailed();

    expect(Food::query()->count())->toBe(0);
});

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
    foreach (glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'food-translation-command-*') ?: [] as $path) {
        @unlink($path);
    }
});

function foodTranslationCommandCsv(string $contents): string
{
    $path = tempnam(sys_get_temp_dir(), 'food-translation-command-');

    if ($path === false) {
        throw new RuntimeException('Could not create CSV fixture.');
    }

    File::put($path, $contents);

    return $path;
}

/*
|--------------------------------------------------------------------------
| Tests
|--------------------------------------------------------------------------
*/

it('imports food translations using the default source and version', function () {
    $firstFood = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '001',
        'source_version' => '4',
        'name_en' => null,
    ]);
    $secondFood = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '002',
        'source_version' => '4',
        'name_en' => null,
    ]);
    $csvPath = foodTranslationCommandCsv(<<<CSV
        source_code,name_en
        001,Skim milk
        002,Whole milk
        CSV);

    $this->artisan('foods:import-translations', [
        'path' => $csvPath,
    ])
        ->expectsOutput('Imported 2 food translations.')
        ->assertSuccessful();

    expect($firstFood->refresh()->name_en)->toBe('Skim milk');
    expect($secondFood->refresh()->name_en)->toBe('Whole milk');
});

it('does not persist translations during a valid dry run', function () {
    $firstFood = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '003',
        'source_version' => '4',
        'name_en' => null,
    ]);
    $secondFood = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '004',
        'source_version' => '4',
        'name_en' => null,
    ]);
    $csvPath = foodTranslationCommandCsv(<<<CSV
        source_code,name_en
        003,White rice
        004,Brown rice
        CSV);

    $this->artisan('foods:import-translations', [
        'path' => $csvPath,
        '--dry-run' => true,
    ])
        ->expectsOutput('Dry run: 2 food translations would be imported.')
        ->assertSuccessful();

    expect($firstFood->refresh()->name_en)->toBeNull();
    expect($secondFood->refresh()->name_en)->toBeNull();
});

it('fails a dry run without updating foods when the translation CSV is invalid', function () {
    $firstFood = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '005',
        'source_version' => '4',
        'name_en' => 'Existing first translation',
    ]);
    $secondFood = Food::factory()->create([
        'data_source' => 'taco',
        'source_code' => '006',
        'source_version' => '4',
        'name_en' => 'Existing second translation',
    ]);
    $csvPath = foodTranslationCommandCsv(<<<CSV
        source_code,name_en
        005,Updated first translation
        006,   
        CSV);

    $this->artisan('foods:import-translations', [
        'path' => $csvPath,
        '--dry-run' => true,
    ])
        ->expectsOutputToContain('Invalid CSV row')
        ->assertFailed();

    expect($firstFood->refresh()->name_en)->toBe('Existing first translation');
    expect($secondFood->refresh()->name_en)->toBe('Existing second translation');
});

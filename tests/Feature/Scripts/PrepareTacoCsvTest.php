<?php

require_once dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepare_taco_csv.php';

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function prepareTacoCsvFixture(string $contents): string
{
    $path = tempnam(sys_get_temp_dir(), 'prepare-taco-input-');

    if ($path === false) {
        throw new RuntimeException('Could not create input fixture.');
    }

    file_put_contents($path, $contents);

    return $path;
}

function prepareTacoCsvOutputPath(): string
{
    $path = tempnam(sys_get_temp_dir(), 'prepare-taco-output-');

    if ($path === false) {
        throw new RuntimeException('Could not create output fixture.');
    }

    unlink($path);

    return $path;
}

function readPrepareTacoCsv(string $path): array
{
    $handle = fopen($path, 'r');
    $rows = [];

    while (($row = fgetcsv($handle)) !== false) {
        $rows[] = $row;
    }

    fclose($handle);

    return $rows;
}

function tacoInputWithRows(string $rows): string
{
    return <<<CSV
numero_alimento,descricao,energia_kcal,proteina_g,carboidrato_g,lipideos_g
{$rows}
CSV;
}

function tacoOverridesWithRows(string $rows = ''): string
{
    return <<<CSV
source_code,action,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g,nutrient_source,source_reference,notes
{$rows}
CSV;
}

function prepareTacoCsvOverridesFixture(string $rows = ''): string
{
    return prepareTacoCsvFixture(tacoOverridesWithRows($rows));
}

afterEach(function () {
    foreach (glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'prepare-taco-*') ?: [] as $path) {
        @unlink($path);
    }
});

/*
|--------------------------------------------------------------------------
| Tests
|--------------------------------------------------------------------------
*/

it('generates the expected header and maps input columns', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('1,"Arroz, cozido",123.5,2.5,25,1'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();

    $result = prepareTacoCsv($input, $output, $overrides);

    expect(readPrepareTacoCsv($output))->toBe([
        ['source_code', 'name_pt', 'name_en', 'calories_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g'],
        ['1', 'Arroz, cozido', '', '123.5', '2.5', '25', '1'],
    ])->and($result)->toBe(['records' => 1, 'overridden' => 0, 'removed' => 0, 'empty_calories' => 0]);
});

it('removes external spaces from Portuguese food names while preserving their data', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('20,"  Couve, manteiga, refogada  ",29,1.7,4.4,0.9'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();

    prepareTacoCsv($input, $output, $overrides);

    expect(readPrepareTacoCsv($output)[1])->toBe([
        '20',
        'Couve, manteiga, refogada',
        '',
        '29',
        '1.7',
        '4.4',
        '0.9',
    ]);
});

it('writes name en as an empty field', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('2,Banana,89,1.1,23,0.3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();

    prepareTacoCsv($input, $output, $overrides);

    expect(readPrepareTacoCsv($output)[1][2])->toBe('');
});

it('converts negative carbohydrates to zero', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('3,Alimento,10,1,-0.25,2'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();

    prepareTacoCsv($input, $output, $overrides);

    expect(readPrepareTacoCsv($output)[1][5])->toBe('0');
});

it('preserves positive carbohydrates and scientific notation', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows("4,Positivo,10,1,0,2\n5,Cientifico,10,1,1e-05,2"));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();

    prepareTacoCsv($input, $output, $overrides);

    $rows = readPrepareTacoCsv($output);

    expect($rows[1][5])->toBe('0')
        ->and($rows[2][5])->toBe('1e-05');
});

it('preserves empty nutritional fields', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('6,Incompleto,,,,,'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();

    $result = prepareTacoCsv($input, $output, $overrides);

    expect(readPrepareTacoCsv($output)[1])->toBe(['6', 'Incompleto', '', '', '', '', ''])
        ->and($result)->toBe(['records' => 1, 'overridden' => 0, 'removed' => 0, 'empty_calories' => 1]);
});

it('fails clearly without creating output when a required input column is missing', function () {
    $input = prepareTacoCsvFixture("numero_alimento,descricao,energia_kcal,proteina_g,carboidrato_g\n1,Alimento,10,1,2\n");
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();

    expect(fn () => prepareTacoCsv($input, $output, $overrides))
        ->toThrow(InvalidArgumentException::class, 'Missing required column: lipideos_g')
        ->and(file_exists($output))->toBeFalse();
});

it('keeps every input row including incomplete records', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows("7,Completo,100,2,3,4\n8,Sem calorias,,2,3,4\n9,Sem proteina,100,,3,4"));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();

    $result = prepareTacoCsv($input, $output, $overrides);

    expect(readPrepareTacoCsv($output))->toHaveCount(4)
        ->and($result)->toBe(['records' => 3, 'overridden' => 0, 'removed' => 0, 'empty_calories' => 1]);
});

it('applies an override while preserving the food identity fields', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('10,Leite original,50,3,4,2'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture('10,override,39,2.98,5.95,0.36,usda,171269,Updated values');

    $result = prepareTacoCsv($input, $output, $overrides);

    expect(readPrepareTacoCsv($output)[1])->toBe(['10', 'Leite original', '', '39', '2.98', '5.95', '0.36'])
        ->and($result)->toBe(['records' => 1, 'overridden' => 1, 'removed' => 0, 'empty_calories' => 0]);
});

it('removes a record declared by an override decision', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows("11,Manter,10,1,2,3\n12,Remover,20,2,3,4"));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture('12,remove,,,,,taco,,No caloric use');

    $result = prepareTacoCsv($input, $output, $overrides);

    expect(readPrepareTacoCsv($output))->toBe([
        ['source_code', 'name_pt', 'name_en', 'calories_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g'],
        ['11', 'Manter', '', '10', '1', '2', '3'],
    ])->and($result)->toBe(['records' => 1, 'overridden' => 0, 'removed' => 1, 'empty_calories' => 0]);
});

it('fails when the overrides CSV cannot be opened', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('13,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = sys_get_temp_dir().DIRECTORY_SEPARATOR.'missing-taco-overrides.csv';

    expect(fn () => prepareTacoCsv($input, $output, $overrides))
        ->toThrow(RuntimeException::class, 'Could not open overrides CSV');
});

it('fails when a required overrides column is missing', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('14,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvFixture("source_code,action,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g,nutrient_source,source_reference\n");

    expect(fn () => prepareTacoCsv($input, $output, $overrides))
        ->toThrow(InvalidArgumentException::class, 'Missing required overrides column: notes');
});

it('fails when an override source code is duplicated', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('15,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture("15,remove,,,,,taco,,First\n15,remove,,,,,taco,,Second");

    expect(fn () => prepareTacoCsv($input, $output, $overrides))
        ->toThrow(InvalidArgumentException::class, 'Duplicate override source_code: 15');
});

it('fails when an override action is invalid', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('16,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture('16,replace,,,,,taco,,Invalid action');

    expect(fn () => prepareTacoCsv($input, $output, $overrides))
        ->toThrow(InvalidArgumentException::class, 'Invalid override action for source_code 16: replace');
});

it('fails when an override nutritional value is missing', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('17,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture('17,override,,2,3,4,usda,REF,Missing calories');

    expect(fn () => prepareTacoCsv($input, $output, $overrides))
        ->toThrow(InvalidArgumentException::class, 'Override source_code 17 requires calories_per_100g');
});

it('fails when an override nutritional value is not numeric', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('18,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture('18,override,10,invalid,3,4,usda,REF,Invalid protein');

    expect(fn () => prepareTacoCsv($input, $output, $overrides))
        ->toThrow(InvalidArgumentException::class, 'Override source_code 18 has non-numeric protein_per_100g');
});

it('fails when an override source code is not in the input CSV', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('19,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture('99,remove,,,,,taco,,Missing food');

    expect(fn () => prepareTacoCsv($input, $output, $overrides))
        ->toThrow(InvalidArgumentException::class, 'Override source_code not found in input CSV: 99');
});

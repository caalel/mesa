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

function tacoTranslationsWithRows(string $rows = ''): string
{
    return <<<CSV
        source_code,name_en
        {$rows}
        CSV;
}

function prepareTacoCsvTranslationsFixture(string $rows = ''): string
{
    return prepareTacoCsvFixture(tacoTranslationsWithRows($rows));
}

function prepareTacoCsvTranslationsForSourceCodes(string ...$sourceCodes): string
{
    return prepareTacoCsvTranslationsFixture(implode("\n", array_map(
        fn (string $sourceCode): string => "{$sourceCode},English food {$sourceCode}",
        $sourceCodes,
    )));
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
    $translations = prepareTacoCsvTranslationsForSourceCodes('1');

    $result = prepareTacoCsv($input, $output, $overrides, $translations);

    expect(readPrepareTacoCsv($output))->toBe([
        ['source_code', 'name_pt', 'name_en', 'calories_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g'],
        ['1', 'Arroz, cozido', 'English food 1', '123.5', '2.5', '25', '1'],
    ])->and($result)->toBe(['records' => 1, 'overridden' => 0, 'removed' => 0, 'empty_calories' => 0]);
});

it('removes external spaces from Portuguese food names while preserving their data', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('20,"  Couve, manteiga, refogada  ",29,1.7,4.4,0.9'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();
    $translations = prepareTacoCsvTranslationsForSourceCodes('20');

    prepareTacoCsv($input, $output, $overrides, $translations);

    expect(readPrepareTacoCsv($output)[1])->toBe([
        '20',
        'Couve, manteiga, refogada',
        'English food 20',
        '29',
        '1.7',
        '4.4',
        '0.9',
    ]);
});

it('writes the translated English food name', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('2,Banana,89,1.1,23,0.3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();
    $translations = prepareTacoCsvTranslationsFixture('2,Banana');

    prepareTacoCsv($input, $output, $overrides, $translations);

    expect(readPrepareTacoCsv($output)[1][2])->toBe('Banana');
});

it('converts negative carbohydrates to zero', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('3,Alimento,10,1,-0.25,2'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();
    $translations = prepareTacoCsvTranslationsForSourceCodes('3');

    prepareTacoCsv($input, $output, $overrides, $translations);

    expect(readPrepareTacoCsv($output)[1][5])->toBe('0');
});

it('preserves positive carbohydrates and scientific notation', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows("4,Positivo,10,1,0,2\n5,Cientifico,10,1,1e-05,2"));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();
    $translations = prepareTacoCsvTranslationsForSourceCodes('4', '5');

    prepareTacoCsv($input, $output, $overrides, $translations);

    $rows = readPrepareTacoCsv($output);

    expect($rows[1][5])->toBe('0')
        ->and($rows[2][5])->toBe('1e-05');
});

it('preserves empty nutritional fields', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('6,Incompleto,,,,,'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();
    $translations = prepareTacoCsvTranslationsForSourceCodes('6');

    $result = prepareTacoCsv($input, $output, $overrides, $translations);

    expect(readPrepareTacoCsv($output)[1])->toBe(['6', 'Incompleto', 'English food 6', '', '', '', ''])
        ->and($result)->toBe(['records' => 1, 'overridden' => 0, 'removed' => 0, 'empty_calories' => 1]);
});

it('fails clearly without creating output when a required input column is missing', function () {
    $input = prepareTacoCsvFixture("numero_alimento,descricao,energia_kcal,proteina_g,carboidrato_g\n1,Alimento,10,1,2\n");
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();
    $translations = prepareTacoCsvTranslationsForSourceCodes('1');

    expect(fn () => prepareTacoCsv($input, $output, $overrides, $translations))
        ->toThrow(InvalidArgumentException::class, 'Missing required column: lipideos_g')
        ->and(file_exists($output))->toBeFalse();
});

it('keeps every input row including incomplete records', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows("7,Completo,100,2,3,4\n8,Sem calorias,,2,3,4\n9,Sem proteina,100,,3,4"));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();
    $translations = prepareTacoCsvTranslationsForSourceCodes('7', '8', '9');

    $result = prepareTacoCsv($input, $output, $overrides, $translations);

    expect(readPrepareTacoCsv($output))->toHaveCount(4)
        ->and($result)->toBe(['records' => 3, 'overridden' => 0, 'removed' => 0, 'empty_calories' => 1]);
});

it('applies an override while preserving the food identity fields', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('10,Leite original,50,3,4,2'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture('10,override,39,2.98,5.95,0.36,usda,171269,Updated values');
    $translations = prepareTacoCsvTranslationsForSourceCodes('10');

    $result = prepareTacoCsv($input, $output, $overrides, $translations);

    expect(readPrepareTacoCsv($output)[1])->toBe(['10', 'Leite original', 'English food 10', '39', '2.98', '5.95', '0.36'])
        ->and($result)->toBe(['records' => 1, 'overridden' => 1, 'removed' => 0, 'empty_calories' => 0]);
});

it('removes a record declared by an override decision', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows("11,Manter,10,1,2,3\n12,Remover,20,2,3,4"));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture('12,remove,,,,,taco,,No caloric use');
    $translations = prepareTacoCsvTranslationsForSourceCodes('11');

    $result = prepareTacoCsv($input, $output, $overrides, $translations);

    expect(readPrepareTacoCsv($output))->toBe([
        ['source_code', 'name_pt', 'name_en', 'calories_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g'],
        ['11', 'Manter', 'English food 11', '10', '1', '2', '3'],
    ])->and($result)->toBe(['records' => 1, 'overridden' => 0, 'removed' => 1, 'empty_calories' => 0]);
});

it('fails when the overrides CSV cannot be opened', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('13,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = sys_get_temp_dir().DIRECTORY_SEPARATOR.'missing-taco-overrides.csv';
    $translations = prepareTacoCsvTranslationsForSourceCodes('13');

    expect(fn () => prepareTacoCsv($input, $output, $overrides, $translations))
        ->toThrow(RuntimeException::class, 'Could not open overrides CSV');
});

it('fails when a required overrides column is missing', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('14,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvFixture("source_code,action,calories_per_100g,protein_per_100g,carbs_per_100g,fat_per_100g,nutrient_source,source_reference\n");
    $translations = prepareTacoCsvTranslationsForSourceCodes('14');

    expect(fn () => prepareTacoCsv($input, $output, $overrides, $translations))
        ->toThrow(InvalidArgumentException::class, 'Missing required overrides column: notes');
});

it('fails when an override source code is duplicated', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('15,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture("15,remove,,,,,taco,,First\n15,remove,,,,,taco,,Second");
    $translations = prepareTacoCsvTranslationsForSourceCodes('15');

    expect(fn () => prepareTacoCsv($input, $output, $overrides, $translations))
        ->toThrow(InvalidArgumentException::class, 'Duplicate override source_code: 15');
});

it('fails when an override action is invalid', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('16,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture('16,replace,,,,,taco,,Invalid action');
    $translations = prepareTacoCsvTranslationsForSourceCodes('16');

    expect(fn () => prepareTacoCsv($input, $output, $overrides, $translations))
        ->toThrow(InvalidArgumentException::class, 'Invalid override action for source_code 16: replace');
});

it('fails when an override nutritional value is missing', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('17,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture('17,override,,2,3,4,usda,REF,Missing calories');
    $translations = prepareTacoCsvTranslationsForSourceCodes('17');

    expect(fn () => prepareTacoCsv($input, $output, $overrides, $translations))
        ->toThrow(InvalidArgumentException::class, 'Override source_code 17 requires calories_per_100g');
});

it('fails when an override nutritional value is not numeric', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('18,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture('18,override,10,invalid,3,4,usda,REF,Invalid protein');
    $translations = prepareTacoCsvTranslationsForSourceCodes('18');

    expect(fn () => prepareTacoCsv($input, $output, $overrides, $translations))
        ->toThrow(InvalidArgumentException::class, 'Override source_code 18 has non-numeric protein_per_100g');
});

it('fails when an override source code is not in the input CSV', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('19,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture('99,remove,,,,,taco,,Missing food');
    $translations = prepareTacoCsvTranslationsForSourceCodes('19');

    expect(fn () => prepareTacoCsv($input, $output, $overrides, $translations))
        ->toThrow(InvalidArgumentException::class, 'Override source_code not found in input CSV: 99');
});

it('fills English food names by source code while preserving the final input order and nutrients', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows("10,Arroz integral cozido,123.5,2.5,25,1\n20,Couve refogada,29,1.7,4.4,0.9\n30,Alimento removido,50,3,4,2"));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture('30,remove,,,,,taco,,Removed from the final CSV');
    $translations = prepareTacoCsvTranslationsFixture("20,Sauteed collard greens\n10,Cooked brown rice");

    prepareTacoCsv($input, $output, $overrides, $translations);

    expect(readPrepareTacoCsv($output))->toBe([
        ['source_code', 'name_pt', 'name_en', 'calories_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g'],
        ['10', 'Arroz integral cozido', 'Cooked brown rice', '123.5', '2.5', '25', '1'],
        ['20', 'Couve refogada', 'Sauteed collard greens', '29', '1.7', '4.4', '0.9'],
    ]);
});

it('fails without creating output when a final food is missing an English translation', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows("31,Primeiro alimento,10,1,2,3\n32,Segundo alimento,20,2,3,4"));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();
    $translations = prepareTacoCsvTranslationsFixture('31,First food');

    expect(fn () => prepareTacoCsv($input, $output, $overrides, $translations))
        ->toThrow(InvalidArgumentException::class, 'Missing translation for source_code: 32')
        ->and(file_exists($output))->toBeFalse();
});

it('fails without creating output when a translation source code is not in the final CSV', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('33,Alimento final,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();
    $translations = prepareTacoCsvTranslationsFixture("33,Final food\n99,Unknown food");

    expect(fn () => prepareTacoCsv($input, $output, $overrides, $translations))
        ->toThrow(InvalidArgumentException::class, 'Translation source_code not found in final CSV: 99')
        ->and(file_exists($output))->toBeFalse();
});

it('fails when the translations CSV cannot be opened', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('34,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();
    $translations = sys_get_temp_dir().DIRECTORY_SEPARATOR.'missing-taco-translations.csv';

    if (file_exists($translations)) {
        unlink($translations);
    }

    expect(fn () => prepareTacoCsv($input, $output, $overrides, $translations))
        ->toThrow(RuntimeException::class, 'Could not open translations CSV')
        ->and(file_exists($output))->toBeFalse();
});

it('fails when the translations CSV header is not source code and name_en', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('35,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();
    $translations = prepareTacoCsvFixture("source_code,english_name\n35,Food");

    expect(fn () => prepareTacoCsv($input, $output, $overrides, $translations))
        ->toThrow(InvalidArgumentException::class, 'Translations CSV header must be source_code,name_en')
        ->and(file_exists($output))->toBeFalse();
});

it('fails when a translation source code is empty', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('36,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();
    $translations = prepareTacoCsvTranslationsFixture(',Food');

    expect(fn () => prepareTacoCsv($input, $output, $overrides, $translations))
        ->toThrow(InvalidArgumentException::class, 'Translation source_code cannot be empty')
        ->and(file_exists($output))->toBeFalse();
});

it('fails when an English translation is empty', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('37,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();
    $translations = prepareTacoCsvTranslationsFixture('37,');

    expect(fn () => prepareTacoCsv($input, $output, $overrides, $translations))
        ->toThrow(InvalidArgumentException::class, 'Translation name_en cannot be empty for source_code: 37')
        ->and(file_exists($output))->toBeFalse();
});

it('fails when a translation source code is duplicated', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('38,Alimento,10,1,2,3'));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();
    $translations = prepareTacoCsvTranslationsFixture("38,Food\n38,Other food");

    expect(fn () => prepareTacoCsv($input, $output, $overrides, $translations))
        ->toThrow(InvalidArgumentException::class, 'Duplicate translation source_code: 38')
        ->and(file_exists($output))->toBeFalse();
});

it('preserves an existing output when a final food is missing an English translation', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows("39,Primeiro alimento,10,1,2,3\n40,Segundo alimento,20,2,3,4"));
    $output = prepareTacoCsvOutputPath();
    $overrides = prepareTacoCsvOverridesFixture();
    $translations = prepareTacoCsvTranslationsFixture('39,First food');

    file_put_contents($output, 'existing,content');

    expect(fn () => prepareTacoCsv($input, $output, $overrides, $translations))
        ->toThrow(InvalidArgumentException::class, 'Missing translation for source_code: 40')
        ->and(file_get_contents($output))->toBe('existing,content');
});

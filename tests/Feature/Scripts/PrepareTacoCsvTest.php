<?php

require_once dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepare_taco_csv.php';

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

afterEach(function () {
    foreach (glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'prepare-taco-*') ?: [] as $path) {
        @unlink($path);
    }
});

it('generates the expected header and maps input columns', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('1,"Arroz, cozido",123.5,2.5,25,1'));
    $output = prepareTacoCsvOutputPath();

    $result = prepareTacoCsv($input, $output);

    expect(readPrepareTacoCsv($output))->toBe([
        ['source_code', 'name_pt', 'name_en', 'calories_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g'],
        ['1', 'Arroz, cozido', '', '123.5', '2.5', '25', '1'],
    ])->and($result)->toBe(['records' => 1, 'empty_calories' => 0]);
});

it('writes name en as an empty field', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('2,Banana,89,1.1,23,0.3'));
    $output = prepareTacoCsvOutputPath();

    prepareTacoCsv($input, $output);

    expect(readPrepareTacoCsv($output)[1][2])->toBe('');
});

it('converts negative carbohydrates to zero', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('3,Alimento,10,1,-0.25,2'));
    $output = prepareTacoCsvOutputPath();

    prepareTacoCsv($input, $output);

    expect(readPrepareTacoCsv($output)[1][5])->toBe('0');
});

it('preserves positive carbohydrates and scientific notation', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows("4,Positivo,10,1,0,2\n5,Cientifico,10,1,1e-05,2"));
    $output = prepareTacoCsvOutputPath();

    prepareTacoCsv($input, $output);

    $rows = readPrepareTacoCsv($output);

    expect($rows[1][5])->toBe('0')
        ->and($rows[2][5])->toBe('1e-05');
});

it('preserves empty nutritional fields', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows('6,Incompleto,,,,,'));
    $output = prepareTacoCsvOutputPath();

    $result = prepareTacoCsv($input, $output);

    expect(readPrepareTacoCsv($output)[1])->toBe(['6', 'Incompleto', '', '', '', '', ''])
        ->and($result)->toBe(['records' => 1, 'empty_calories' => 1]);
});

it('fails clearly without creating output when a required input column is missing', function () {
    $input = prepareTacoCsvFixture("numero_alimento,descricao,energia_kcal,proteina_g,carboidrato_g\n1,Alimento,10,1,2\n");
    $output = prepareTacoCsvOutputPath();

    expect(fn () => prepareTacoCsv($input, $output))
        ->toThrow(InvalidArgumentException::class, 'Missing required column: lipideos_g')
        ->and(file_exists($output))->toBeFalse();
});

it('keeps every input row including incomplete records', function () {
    $input = prepareTacoCsvFixture(tacoInputWithRows("7,Completo,100,2,3,4\n8,Sem calorias,,2,3,4\n9,Sem proteina,100,,3,4"));
    $output = prepareTacoCsvOutputPath();

    $result = prepareTacoCsv($input, $output);

    expect(readPrepareTacoCsv($output))->toHaveCount(4)
        ->and($result)->toBe(['records' => 3, 'empty_calories' => 1]);
});

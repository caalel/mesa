<?php

namespace Database\Seeders;

use App\Services\FoodImporter;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(FoodImporter $foodImportService): void
    {
        $foodImportService->import(
            database_path('data/foods/taco-v4.csv'),
            'taco',
            '4',
        );
    }
}

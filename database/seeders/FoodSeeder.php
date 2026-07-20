<?php

namespace Database\Seeders;

use App\Services\FoodImportService;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(FoodImportService $foodImportService): void
    {
        $foodImportService->import(
            database_path('data/foods/taco-v4.csv'),
            'taco',
            '4',
        );
    }
}

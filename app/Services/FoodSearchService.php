<?php

namespace App\Services;

use App\Models\Food;
use Illuminate\Database\Eloquent\Collection;

class FoodSearchService
{
    public function search(string $namePt): Collection
    {
        return Food::query()
                    ->where('name_pt', 'like', "%{$namePt}%")
                    ->orderBy('name_pt')
                    ->limit(8)
                    ->get();
    }
}

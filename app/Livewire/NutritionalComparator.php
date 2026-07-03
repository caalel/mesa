<?php

namespace App\Livewire;

use App\Services\FoodSearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class NutritionalComparator extends Component
{
    public string $foodASearch = '';

    protected FoodSearchService $foodSearchService;

    public function boot(FoodSearchService $foodSearchService): void
    {
        $this->foodSearchService = $foodSearchService;
    }

    public function render(): View
    {
        return view('livewire.nutritional-comparator', [
            'foodAResults' => $this->foodAResults(),
        ]);
    }

    private function foodAResults(): Collection
    {
        if (mb_strlen($this->foodASearch) < 2) {
            return collect();
        }

        return $this->foodSearchService
            ->search($this->foodASearch)
            ->take(8);
    }
}

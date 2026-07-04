<?php

namespace App\Livewire;

use App\Models\Food;
use App\Services\FoodSearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class NutritionalComparator extends Component
{
    public string $foodASearch = '';

    public ?int $foodAId = null;

    protected FoodSearchService $foodSearchService;

    public function boot(FoodSearchService $foodSearchService): void
    {
        $this->foodSearchService = $foodSearchService;
    }

    public function render(): View
    {
        return view('livewire.nutritional-comparator', [
            'selectedFoodA' => $this->selectedFoodA(),
            'foodAResults' => $this->foodAResults(),
        ]);
    }

    public function selectFoodA(int $foodId): void
    {
        $this->foodAId = $foodId;
        $this->foodASearch = '';
    }

    public function changeFoodA(): void
    {
        $this->foodAId = null;
        $this->foodASearch = '';
    }

    private function foodAResults(): Collection
    {
        if ($this->foodAId !== null) {
            return collect();
        }

        if (mb_strlen($this->foodASearch) < 2) {
            return collect();
        }

        return $this->foodSearchService
            ->search($this->foodASearch)
            ->take(8);
    }

    private function selectedFoodA(): ?Food
    {
        if ($this->foodAId === null) {
            return null;
        }

        return Food::find($this->foodAId);
    }
}

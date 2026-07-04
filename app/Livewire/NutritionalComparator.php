<?php

namespace App\Livewire;

use App\Models\Food;
use App\Services\FoodSearchService;
use App\Services\NutritionalValuesCalculatorService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class NutritionalComparator extends Component
{
    public string $foodASearch = '';

    public string $foodAWeight = '';

    public ?int $foodAId = null;

    protected FoodSearchService $foodSearchService;

    protected NutritionalValuesCalculatorService $nutritionalValuesCalculatorService;

    public function boot(
        FoodSearchService $foodSearchService,
        NutritionalValuesCalculatorService $nutritionalValuesCalculatorService,
    ): void {
        $this->foodSearchService = $foodSearchService;
        $this->nutritionalValuesCalculatorService = $nutritionalValuesCalculatorService;
    }

    public function render(): View
    {
        $selectedFoodA = $this->selectedFoodA();

        return view('livewire.nutritional-comparator', [
            'selectedFoodA' => $selectedFoodA,
            'foodAResults' => $this->foodAResults(),
            'foodASummary' => $this->foodASummary($selectedFoodA),
        ]);
    }

    public function selectFoodA(int $foodId): void
    {
        $this->foodAId = $foodId;
        $this->foodASearch = '';
        $this->foodAWeight = '';
    }

    public function changeFoodA(): void
    {
        $this->foodAId = null;
        $this->foodASearch = '';
        $this->foodAWeight = '';
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

    /**
     * @return array{food: Food, weight: int|float, calories: float}|null
     */
    private function foodASummary(?Food $selectedFoodA): ?array
    {
        if ($selectedFoodA === null) {
            return null;
        }

        if (! is_numeric($this->foodAWeight)) {
            return null;
        }

        $weight = (float) $this->foodAWeight;

        if ($weight <= 0) {
            return null;
        }

        return [
            'food' => $selectedFoodA,
            'weight' => $weight,
            'calories' => $this->nutritionalValuesCalculatorService->calculateValue(
                valuePer100g: (float) $selectedFoodA->calories_per_100g,
                weight: $weight,
            ),
        ];
    }
}

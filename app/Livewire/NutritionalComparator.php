<?php

namespace App\Livewire;

use App\Models\Food;
use App\Services\CompareFoodsService;
use App\Services\FoodSearchService;
use App\Services\NutritionalValuesCalculatorService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class NutritionalComparator extends Component
{
    public string $foodASearch = '';

    public string $foodBSearch = '';

    public string $foodAWeight = '';

    public ?int $foodAId = null;

    public ?int $foodBId = null;

    /**
     * @var array{food_a_weight: string, food_a_name: string, food_b_weight: string, food_b_name: string}|null
     */
    public ?array $comparisonResult = null;

    protected CompareFoodsService $compareFoodsService;

    protected FoodSearchService $foodSearchService;

    protected NutritionalValuesCalculatorService $nutritionalValuesCalculatorService;

    public function boot(
        CompareFoodsService $compareFoodsService,
        FoodSearchService $foodSearchService,
        NutritionalValuesCalculatorService $nutritionalValuesCalculatorService,
    ): void {
        $this->compareFoodsService = $compareFoodsService;
        $this->foodSearchService = $foodSearchService;
        $this->nutritionalValuesCalculatorService = $nutritionalValuesCalculatorService;
    }

    public function render(): View
    {
        $selectedFoodA = $this->selectedFoodA();
        $selectedFoodB = $this->selectedFoodB();
        $canCompare = $this->canCompare();

        return view('livewire.nutritional-comparator', [
            'selectedFoodA' => $selectedFoodA,
            'selectedFoodB' => $selectedFoodB,
            'foodAResults' => $this->foodAResults(),
            'foodBResults' => $this->foodBResults(),
            'foodASummary' => $this->foodASummary($selectedFoodA),
            'canCompare' => $canCompare,
            'comparisonResult' => $this->comparisonResult,
        ]);
    }

    public function selectFoodA(int $foodId): void
    {
        $this->foodAId = $foodId;
        $this->foodASearch = '';
        $this->foodAWeight = '';
        $this->comparisonResult = null;
    }

    public function changeFoodA(): void
    {
        $this->foodAId = null;
        $this->foodASearch = '';
        $this->foodAWeight = '';
        $this->comparisonResult = null;
    }

    public function selectFoodB(int $foodId): void
    {
        $this->foodBId = $foodId;
        $this->foodBSearch = '';
        $this->comparisonResult = null;
    }

    public function changeFoodB(): void
    {
        $this->foodBId = null;
        $this->foodBSearch = '';
        $this->comparisonResult = null;
    }

    public function compare(): void
    {
        $this->comparisonResult = null;

        if (! $this->canCompare()) {
            return;
        }

        $foodA = $this->selectedFoodA();
        $foodB = $this->selectedFoodB();

        if ($foodA === null || $foodB === null) {
            return;
        }

        $foodAWeight = (float) $this->foodAWeight;
        $foodBWeight = $this->compareFoodsService->calculateEquivalentWeight(
            foodAValuePer100g: (int) $foodA->calories_per_100g,
            foodAWeight: (int) $foodAWeight,
            foodBValuePer100g: (int) $foodB->calories_per_100g,
        );

        $this->comparisonResult = [
            'food_a_weight' => $this->formatNumber($foodAWeight),
            'food_a_name' => $foodA->name_pt,
            'food_b_weight' => $this->formatNumber($foodBWeight),
            'food_b_name' => $foodB->name_pt,
        ];
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

    private function foodBResults(): Collection
    {
        if ($this->foodBId !== null) {
            return collect();
        }

        if (mb_strlen($this->foodBSearch) < 2) {
            return collect();
        }

        return $this->foodSearchService
            ->search($this->foodBSearch)
            ->take(8);
    }

    private function selectedFoodA(): ?Food
    {
        if ($this->foodAId === null) {
            return null;
        }

        return Food::find($this->foodAId);
    }

    private function selectedFoodB(): ?Food
    {
        if ($this->foodBId === null) {
            return null;
        }

        return Food::find($this->foodBId);
    }

    private function canCompare(): bool
    {
        if ($this->foodAId === null || $this->foodBId === null) {
            return false;
        }

        if (! is_numeric($this->foodAWeight)) {
            return false;
        }

        return (float) $this->foodAWeight > 0;
    }

    private function formatNumber(int|float $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
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

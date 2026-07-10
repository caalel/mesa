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
    private const MAX_FOOD_A_WEIGHT_IN_GRAMS = 10000;

    private const MIN_DISPLAYABLE_POSITIVE_VALUE = 0.01;

    public string $foodASearch = '';

    public string $foodBSearch = '';

    public string $foodAWeight = '';

    public ?int $foodAId = null;

    public ?int $foodBId = null;

    /**
     * @var array{food_a_weight: string, food_a_name: string, food_b_weight: string, food_b_name: string, food_b_weight_is_less_than_minimum: bool}|null
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
        $foodAHasUnavailableCalorieData = $this->foodHasUnavailableCalorieData($selectedFoodA);
        $foodBHasUnavailableCalorieData = $this->foodHasUnavailableCalorieData($selectedFoodB);
        $canCompare = $this->canCompare();
        $foodAResults = $this->foodAResults();
        $foodBResults = $this->foodBResults();

        return view('livewire.nutritional-comparator', [
            'selectedFoodA' => $selectedFoodA,
            'selectedFoodB' => $selectedFoodB,
            'foodAResults' => $foodAResults,
            'foodBResults' => $foodBResults,
            'foodASummary' => $this->foodASummary($selectedFoodA),
            'foodAHasUnavailableCalorieData' => $foodAHasUnavailableCalorieData,
            'foodBHasUnavailableCalorieData' => $foodBHasUnavailableCalorieData,
            'canCompare' => $canCompare,
            'comparisonResult' => $this->comparisonResult,
            'foodAHasNoResults' => $this->hasNoSearchResults($this->foodAId, $this->foodASearch, $foodAResults),
            'foodBHasNoResults' => $this->hasNoSearchResults($this->foodBId, $this->foodBSearch, $foodBResults),
            'foodAShouldShowSearchHelp' => $this->shouldShowSearchHelp($this->foodAId, $this->foodASearch),
            'foodBShouldShowSearchHelp' => $this->shouldShowSearchHelp($this->foodBId, $this->foodBSearch),
            'foodAWeightValidationMessage' => $this->foodAWeightValidationMessage(),
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

        if ($this->foodHasUnavailableCalorieData($foodA) || $this->foodHasUnavailableCalorieData($foodB)) {
            return;
        }

        $foodAWeight = (float) $this->foodAWeight;
        $foodBWeight = $this->compareFoodsService->calculateEquivalentWeight(
            foodAValuePer100g: (float) $foodA->calories_per_100g,
            foodAWeight: $foodAWeight,
            foodBValuePer100g: (float) $foodB->calories_per_100g,
        );
        $foodBWeightIsLessThanMinimum = $this->isPositiveValueBelowDisplayMinimum($foodBWeight);

        $this->comparisonResult = [
            'food_a_weight' => $this->formatNumber($foodAWeight),
            'food_a_name' => $foodA->name_pt,
            'food_b_weight' => $foodBWeightIsLessThanMinimum ? $this->formatNumber(self::MIN_DISPLAYABLE_POSITIVE_VALUE) : $this->formatNumber($foodBWeight),
            'food_b_name' => $foodB->name_pt,
            'food_b_weight_is_less_than_minimum' => $foodBWeightIsLessThanMinimum,
        ];

        $this->dispatch('comparison-result-shown');
    }

    public function updatedFoodAWeight(): void
    {
        $this->comparisonResult = null;
    }

    private function foodAResults(): Collection
    {
        return $this->searchResults($this->foodAId, $this->foodASearch);
    }

    private function foodBResults(): Collection
    {
        return $this->searchResults($this->foodBId, $this->foodBSearch);
    }

    private function searchResults(?int $selectedFoodId, string $search): Collection
    {
        if ($selectedFoodId !== null) {
            return collect();
        }

        $search = trim($search);

        if (mb_strlen($search) < 2) {
            return collect();
        }

        return $this->foodSearchService->search($search);
    }

    private function hasNoSearchResults(?int $selectedFoodId, string $search, Collection $results): bool
    {
        return $selectedFoodId === null
            && mb_strlen(trim($search)) >= 2
            && $results->isEmpty();
    }

    private function shouldShowSearchHelp(?int $selectedFoodId, string $search): bool
    {
        return $selectedFoodId === null && mb_strlen(trim($search)) < 2;
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

        $foodA = $this->selectedFoodA();
        $foodB = $this->selectedFoodB();

        if ($this->foodHasUnavailableCalorieData($foodA) || $this->foodHasUnavailableCalorieData($foodB)) {
            return false;
        }

        return $this->hasValidFoodAWeight();
    }

    private function foodHasUnavailableCalorieData(?Food $food): bool
    {
        return $food !== null && (float) $food->calories_per_100g <= 0;
    }

    private function hasValidFoodAWeight(): bool
    {
        if (! is_numeric($this->foodAWeight)) {
            return false;
        }

        $weight = (float) $this->foodAWeight;

        return $weight > 0 && $weight <= self::MAX_FOOD_A_WEIGHT_IN_GRAMS;
    }

    private function foodAWeightExceedsMaximum(): bool
    {
        return is_numeric($this->foodAWeight)
            && (float) $this->foodAWeight > self::MAX_FOOD_A_WEIGHT_IN_GRAMS;
    }

    private function foodAWeightValidationMessage(): ?string
    {
        if ($this->foodAWeight === '') {
            return null;
        }

        if (! is_numeric($this->foodAWeight)) {
            return __('ui.compare.quantity_must_be_numeric');
        }

        if ((float) $this->foodAWeight <= 0) {
            return __('ui.compare.quantity_must_be_positive');
        }

        if ($this->foodAWeightExceedsMaximum()) {
            return __('ui.compare.quantity_too_high', [
                'max' => $this->formatNumber(self::MAX_FOOD_A_WEIGHT_IN_GRAMS),
            ]);
        }

        return null;
    }

    private function formatNumber(int|float $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
    }

    private function formatDisplayNumber(int|float $value): string
    {
        if ($this->isPositiveValueBelowDisplayMinimum($value)) {
            return '< '.$this->formatNumber(self::MIN_DISPLAYABLE_POSITIVE_VALUE);
        }

        return $this->formatNumber($value);
    }

    private function isPositiveValueBelowDisplayMinimum(int|float $value): bool
    {
        $value = (float) $value;

        return $value > 0 && $value < self::MIN_DISPLAYABLE_POSITIVE_VALUE;
    }

    /**
     * @return array{food: Food, weight: int|float, calories: float, formatted_weight: string, formatted_calories: string}|null
     */
    private function foodASummary(?Food $selectedFoodA): ?array
    {
        if ($selectedFoodA === null) {
            return null;
        }

        if ($this->foodHasUnavailableCalorieData($selectedFoodA)) {
            return null;
        }

        if (! is_numeric($this->foodAWeight)) {
            return null;
        }

        $weight = (float) $this->foodAWeight;

        if (! $this->hasValidFoodAWeight()) {
            return null;
        }

        $calories = $this->nutritionalValuesCalculatorService->calculateValue(
            valuePer100g: (float) $selectedFoodA->calories_per_100g,
            weight: $weight,
        );

        return [
            'food' => $selectedFoodA,
            'weight' => $weight,
            'calories' => $calories,
            'formatted_weight' => $this->formatNumber($weight),
            'formatted_calories' => $this->formatDisplayNumber($calories),
        ];
    }
}

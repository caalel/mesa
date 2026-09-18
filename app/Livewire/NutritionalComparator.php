<?php

namespace App\Livewire;

use App\Enums\ComparisonNutrient;
use App\Models\Food;
use App\Services\CompareFoodsService;
use App\Services\FoodSearchService;
use App\Services\FoodWeightInputService;
use App\Services\LocalizedNutritionalValueFormatter;
use App\Services\NutritionalValuesCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class NutritionalComparator extends Component
{
    public ComparisonNutrient $selectedNutrient = ComparisonNutrient::Calories;

    public string $foodASearch = '';

    public string $foodBSearch = '';

    public string $foodAWeight = '';

    public ?int $foodAId = null;

    public ?int $foodBId = null;

    protected CompareFoodsService $compareFoodsService;

    protected FoodSearchService $foodSearchService;

    protected FoodWeightInputService $foodWeightInputService;

    protected NutritionalValuesCalculator $nutritionalValuesCalculator;

    protected LocalizedNutritionalValueFormatter $localizedNutritionalValueFormatter;

    public function boot(
        CompareFoodsService $compareFoodsService,
        FoodSearchService $foodSearchService,
        FoodWeightInputService $foodWeightInputService,
        LocalizedNutritionalValueFormatter $localizedNutritionalValueFormatter,
        NutritionalValuesCalculator $nutritionalValuesCalculator,
    ): void {
        $this->compareFoodsService = $compareFoodsService;
        $this->foodSearchService = $foodSearchService;
        $this->foodWeightInputService = $foodWeightInputService;
        $this->localizedNutritionalValueFormatter = $localizedNutritionalValueFormatter;
        $this->nutritionalValuesCalculator = $nutritionalValuesCalculator;
    }

    public function render(): View
    {
        $selectedFoodA = $this->selectedFoodA();
        $selectedFoodB = $this->selectedFoodB();
        $foodAHasUnavailableSelectedNutrient = $this->foodHasUnavailableSelectedNutrient($selectedFoodA);
        $foodBHasUnavailableSelectedNutrient = $this->foodHasUnavailableSelectedNutrient($selectedFoodB);
        $foodAResults = $this->foodAResults();
        $foodBResults = $this->foodBResults();
        $comparisonResult = $this->comparisonResult();

        return view('livewire.nutritional-comparator', [
            'selectedFoodA' => $selectedFoodA,
            'selectedFoodB' => $selectedFoodB,
            'foodAResults' => $foodAResults,
            'foodBResults' => $foodBResults,
            'foodASummary' => $this->foodASummary($selectedFoodA),
            'foodBSummary' => $this->foodBSummary($selectedFoodB, $comparisonResult),
            'foodAHasUnavailableSelectedNutrient' => $foodAHasUnavailableSelectedNutrient,
            'foodBHasUnavailableSelectedNutrient' => $foodBHasUnavailableSelectedNutrient,
            'comparisonNutrients' => ComparisonNutrient::cases(),
            'comparisonResult' => $comparisonResult,
            'foodAHasNoResults' => $this->hasNoSearchResults($this->foodAId, $this->foodASearch, $foodAResults),
            'foodBHasNoResults' => $this->hasNoSearchResults($this->foodBId, $this->foodBSearch, $foodBResults),
            'foodAWeightValidationMessage' => $this->foodAWeightValidationMessage(),
        ])->layout('layouts.app', [
            'title' => __('ui.metadata.comparator.title'),
            'description' => __('ui.metadata.comparator.description'),
        ]);
    }

    public function selectFoodA(int $foodId): void
    {
        $this->foodAId = $foodId;
        $this->foodASearch = '';
        $this->foodAWeight = '';

        $this->dispatchComparisonResultAvailable();
    }

    public function changeFoodA(): void
    {
        $this->foodAId = null;
        $this->foodASearch = '';
        $this->foodAWeight = '';
    }

    public function selectFoodB(int $foodId): void
    {
        $this->foodBId = $foodId;
        $this->foodBSearch = '';

        $this->dispatchComparisonResultAvailable();
    }

    public function changeFoodB(): void
    {
        $this->foodBId = null;
        $this->foodBSearch = '';
    }

    public function updatedFoodAWeight(): void
    {
        $this->dispatchComparisonResultAvailable();
    }

    public function updatedSelectedNutrient(): void
    {
        $this->dispatchComparisonResultAvailable();
    }

    private function dispatchComparisonResultAvailable(): void
    {
        if ($this->comparisonResult() !== null) {
            $this->dispatch('comparison-result-available');
        }
    }

    /**
     * @return array{food_a_weight: string, food_a_name: string, food_b_weight: string, food_b_weight_value: float, food_b_name: string, food_b_weight_is_less_than_minimum: bool}|null
     */
    private function comparisonResult(): ?array
    {
        if ($this->foodAId === null || $this->foodBId === null) {
            return null;
        }

        $foodA = $this->selectedFoodA();
        $foodB = $this->selectedFoodB();

        if ($foodA === null || $foodB === null || ! $this->foodWeightInputService->isValid($this->foodAWeight)) {
            return null;
        }

        if ($this->foodHasUnavailableSelectedNutrient($foodA) || $this->foodHasUnavailableSelectedNutrient($foodB)) {
            return null;
        }

        $nutrientAttribute = $this->selectedNutrient->foodAttribute();
        $foodAWeight = (float) $this->foodWeightInputService->normalize($this->foodAWeight);
        $foodBWeight = $this->compareFoodsService->calculateEquivalentWeight(
            foodAValuePer100g: (float) $foodA->{$nutrientAttribute},
            foodAWeight: $foodAWeight,
            foodBValuePer100g: (float) $foodB->{$nutrientAttribute},
        );
        $foodBWeightIsLessThanMinimum = $this->localizedNutritionalValueFormatter->isPositiveValueBelowDisplayMinimum($foodBWeight);

        return [
            'food_a_weight' => $this->localizedNutritionalValueFormatter->format($foodAWeight),
            'food_a_name' => $foodA->localized_name,
            'food_b_weight' => $foodBWeightIsLessThanMinimum ? $this->localizedNutritionalValueFormatter->format(LocalizedNutritionalValueFormatter::MINIMUM_DISPLAYABLE_POSITIVE_VALUE) : $this->localizedNutritionalValueFormatter->format($foodBWeight),
            'food_b_weight_value' => $foodBWeight,
            'food_b_name' => $foodB->localized_name,
            'food_b_weight_is_less_than_minimum' => $foodBWeightIsLessThanMinimum,
        ];
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

        if ($search === '') {
            return collect();
        }

        return $this->foodSearchService->search($search);
    }

    private function hasNoSearchResults(?int $selectedFoodId, string $search, Collection $results): bool
    {
        return $selectedFoodId === null
            && trim($search) !== ''
            && $results->isEmpty();
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

    private function foodHasUnavailableSelectedNutrient(?Food $food): bool
    {
        if ($food === null) {
            return false;
        }

        $nutrientAttribute = $this->selectedNutrient->foodAttribute();

        return (float) $food->{$nutrientAttribute} <= 0;
    }

    private function foodAWeightValidationMessage(): ?string
    {
        $weight = $this->foodWeightInputService->normalize($this->foodAWeight);

        if ($weight === '') {
            return null;
        }

        if (! $this->foodWeightInputService->isNumeric($weight)) {
            return __('ui.compare.quantity_must_be_numeric');
        }

        if (! $this->foodWeightInputService->isPositive($weight)) {
            return __('ui.compare.quantity_must_be_positive');
        }

        if ($this->foodWeightInputService->exceedsMaximum($weight)) {
            return __('ui.compare.quantity_too_high', [
                'max' => $this->localizedNutritionalValueFormatter->format(FoodWeightInputService::MAXIMUM_IN_GRAMS),
            ]);
        }

        return null;
    }

    /**
     * @return array{food: Food, weight: float, formatted_weight: string, formatted_calories: string, formatted_protein: string, formatted_carbs: string, formatted_fat: string}|null
     */
    private function foodASummary(?Food $selectedFoodA): ?array
    {
        if ($selectedFoodA === null) {
            return null;
        }

        $weight = $this->foodWeightInputService->normalize($this->foodAWeight);

        if (! $this->foodWeightInputService->isValid($weight)) {
            return null;
        }

        return $this->foodSummary($selectedFoodA, (float) $weight);
    }

    /**
     * @param  array{food_b_weight_value: float}|null  $comparisonResult
     * @return array{food: Food, weight: float, formatted_weight: string, formatted_calories: string, formatted_protein: string, formatted_carbs: string, formatted_fat: string}|null
     */
    private function foodBSummary(?Food $selectedFoodB, ?array $comparisonResult): ?array
    {
        if ($selectedFoodB === null) {
            return null;
        }

        return $this->foodSummary($selectedFoodB, $comparisonResult['food_b_weight_value'] ?? 100.0);
    }

    /**
     * @return array{food: Food, weight: float, formatted_weight: string, formatted_calories: string, formatted_protein: string, formatted_carbs: string, formatted_fat: string}
     */
    private function foodSummary(Food $food, float $weight): array
    {
        $calories = $this->nutritionalValuesCalculator->calculateValue((float) $food->calories_per_100g, $weight);
        $protein = $this->nutritionalValuesCalculator->calculateValue((float) $food->protein_per_100g, $weight);
        $carbs = $this->nutritionalValuesCalculator->calculateValue((float) $food->carbs_per_100g, $weight);
        $fat = $this->nutritionalValuesCalculator->calculateValue((float) $food->fat_per_100g, $weight);

        return [
            'food' => $food,
            'weight' => $weight,
            'formatted_weight' => $this->localizedNutritionalValueFormatter->formatDisplayValue($weight),
            'formatted_calories' => $this->localizedNutritionalValueFormatter->formatDisplayValue($calories),
            'formatted_protein' => $this->localizedNutritionalValueFormatter->formatDisplayValue($protein),
            'formatted_carbs' => $this->localizedNutritionalValueFormatter->formatDisplayValue($carbs),
            'formatted_fat' => $this->localizedNutritionalValueFormatter->formatDisplayValue($fat),
        ];
    }
}

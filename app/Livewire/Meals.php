<?php

namespace App\Livewire;

use App\Models\Food;
use App\Services\FoodSearchService;
use App\Services\FoodWeightInputService;
use App\Services\LocalizedNutritionalValueFormatter;
use App\Services\NutritionalValuesCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Meals extends Component
{
    private const MEAL_ITEMS_LIMIT = 10;

    public bool $isMealEditorOpen = false;

    public bool $isFoodModalOpen = false;

    public ?string $editingMealId = null;

    public string $mealName = '';

    public string $foodSearch = '';

    public ?int $selectedFoodId = null;

    public string $foodWeight = '';

    public array $mealItems = [];

    protected FoodSearchService $foodSearchService;

    protected NutritionalValuesCalculator $nutritionalValuesCalculator;

    protected FoodWeightInputService $foodWeightInputService;

    protected LocalizedNutritionalValueFormatter $localizedNutritionalValueFormatter;

    public function boot(
        FoodSearchService $foodSearchService,
        FoodWeightInputService $foodWeightInputService,
        LocalizedNutritionalValueFormatter $localizedNutritionalValueFormatter,
        NutritionalValuesCalculator $nutritionalValuesCalculator,
    ): void
    {
        $this->foodSearchService = $foodSearchService;
        $this->foodWeightInputService = $foodWeightInputService;
        $this->localizedNutritionalValueFormatter = $localizedNutritionalValueFormatter;
        $this->nutritionalValuesCalculator = $nutritionalValuesCalculator;
    }

    public function render(): View
    {
        $selectedFood = $this->selectedFood();

        return view('livewire.meals', [
            'foodSearchResults' => $this->foodSearchResults(),
            'selectedFood' => $selectedFood,
            'selectedFoodNutritionPreview' => $this->selectedFoodNutritionPreview($selectedFood),
            'foodWeightValidationMessage' => $this->foodWeightValidationMessage(),
            'hasMealItems' => $this->hasMealItems(),
            'mealItemsCount' => $this->mealItemsCount(),
            'mealItemsLimit' => self::MEAL_ITEMS_LIMIT,
        ]);
    }

    public function createMeal(): void
    {
        $this->isMealEditorOpen = true;
        $this->editingMealId = null;
        $this->mealName = '';
        $this->mealItems = [];
    }

    public function openFoodModal(): void
    {
        $this->isFoodModalOpen = true;
    }

    public function cancelFoodModal(): void
    {
        $this->isFoodModalOpen = false;
        $this->foodSearch = '';
        $this->selectedFoodId = null;
        $this->foodWeight = '';
    }

    public function selectFood(int $foodId): void
    {
        $this->selectedFoodId = $foodId;
        $this->foodWeight = '100';

        $this->dispatch('selected-food-details-shown');
    }

    public function cancelMealEditor(): void
    {
        $this->isMealEditorOpen = false;
        $this->editingMealId = null;
        $this->mealName = '';
        $this->mealItems = [];
    }

    private function foodSearchResults(): Collection
    {
        $search = trim($this->foodSearch);

        if ($search === '') {
            return collect();
        }

        return $this->foodSearchService->search($search);
    }

    private function hasMealItems(): bool
    {
        return $this->mealItems !== [];
    }

    private function mealItemsCount(): int
    {
        return count($this->mealItems);
    }

    private function selectedFood(): ?Food
    {
        if ($this->selectedFoodId === null) {
            return null;
        }

        return Food::find($this->selectedFoodId);
    }

    /**
     * @return array{formatted_weight: string, calories: string, protein: string, carbs: string, fat: string}|null
     */
    private function selectedFoodNutritionPreview(?Food $selectedFood): ?array
    {
        $weight = $this->foodWeightInputService->normalize($this->foodWeight);

        if ($selectedFood === null || ! $this->foodWeightInputService->isValid($weight)) {
            return null;
        }

        $weight = (float) $weight;

        return [
            'formatted_weight' => $this->localizedNutritionalValueFormatter->format($weight),
            'calories' => $this->formatPreviewValue((float) $selectedFood->calories_per_100g, $weight),
            'protein' => $this->formatPreviewValue((float) $selectedFood->protein_per_100g, $weight),
            'carbs' => $this->formatPreviewValue((float) $selectedFood->carbs_per_100g, $weight),
            'fat' => $this->formatPreviewValue((float) $selectedFood->fat_per_100g, $weight),
        ];
    }

    private function foodWeightValidationMessage(): ?string
    {
        $weight = $this->foodWeightInputService->normalize($this->foodWeight);

        if ($weight === '') {
            return null;
        }

        if (! $this->foodWeightInputService->isNumeric($weight)) {
            return __('ui.meals.quantity_must_be_numeric');
        }

        if (! $this->foodWeightInputService->isPositive($weight)) {
            return __('ui.meals.quantity_must_be_positive');
        }

        if ($this->foodWeightInputService->exceedsMaximum($weight)) {
            return __('ui.meals.quantity_too_high', [
                'max' => $this->localizedNutritionalValueFormatter->format(FoodWeightInputService::MAXIMUM_IN_GRAMS),
            ]);
        }

        return null;
    }

    private function formatPreviewValue(float $valuePer100g, float $weight): string
    {
        return $this->localizedNutritionalValueFormatter->formatDisplayValue(
            $this->nutritionalValuesCalculator->calculateValue($valuePer100g, $weight),
        );
    }
}

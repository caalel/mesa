<?php

namespace App\Livewire;

use App\Models\Food;
use App\Services\FoodSearchService;
use App\Services\FoodWeightInputService;
use App\Services\LocalizedNutritionalValueFormatter;
use App\Services\NutritionalValuesCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
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

    public ?int $editingMealItemFoodId = null;

    #[Locked]
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
            'mealDraftItems' => $this->mealDraftItems(),
            'foodWeightValidationMessage' => $this->foodWeightValidationMessage($selectedFood),
            'canAddFoodToDraft' => $this->canAddFoodToDraft(),
            'canUpdateFoodInDraft' => $this->canUpdateFoodInDraft(),
            'canOpenFoodModal' => $this->canOpenFoodModal(),
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
        if (! $this->canOpenFoodModal()) {
            return;
        }

        $this->editingMealItemFoodId = null;
        $this->isFoodModalOpen = true;
    }

    public function cancelFoodModal(): void
    {
        $this->resetFoodModalState();
    }

    public function addFoodToDraft(): void
    {
        $normalizedWeight = $this->foodWeightInputService->normalize($this->foodWeight);

        if ($this->selectedFoodId === null || ! $this->foodWeightInputService->isValid($normalizedWeight)) {
            return;
        }

        $foodId = $this->selectedFoodId;

        if (Food::find($foodId) === null) {
            return;
        }

        $weight = (float) $normalizedWeight;
        $mealItemIndex = $this->mealItemIndexForFood($foodId);

        if ($mealItemIndex === null && $this->mealItemsCount() >= self::MEAL_ITEMS_LIMIT) {
            return;
        }

        $combinedWeight = $mealItemIndex === null
            ? $weight
            : (float) $this->mealItems[$mealItemIndex]['weight'] + $weight;

        if ($combinedWeight > FoodWeightInputService::MAXIMUM_IN_GRAMS) {
            return;
        }

        if ($mealItemIndex === null) {
            $this->mealItems[] = [
                'food_id' => $foodId,
                'weight' => $weight,
            ];
        } else {
            $this->mealItems[$mealItemIndex]['weight'] = $combinedWeight;
        }

        $this->resetFoodModalState();
    }

    public function removeMealItem(int $foodId): void
    {
        $mealItemIndex = $this->mealItemIndexForFood($foodId);

        if ($mealItemIndex === null) {
            return;
        }

        unset($this->mealItems[$mealItemIndex]);

        $this->mealItems = array_values($this->mealItems);
    }

    public function editMealItem(int $foodId): void
    {
        $mealItemIndex = $this->mealItemIndexForFood($foodId);

        if ($mealItemIndex === null) {
            return;
        }

        $this->editingMealItemFoodId = $foodId;
        $this->selectedFoodId = $foodId;
        $this->foodWeight = (string) (float) $this->mealItems[$mealItemIndex]['weight'];
        $this->foodSearch = '';
        $this->isFoodModalOpen = true;
    }

    public function updateFoodInDraft(): void
    {
        $originalFoodId = $this->editingMealItemFoodId;

        if ($originalFoodId === null) {
            return;
        }

        $originalMealItemIndex = $this->mealItemIndexForFood($originalFoodId);

        if ($originalMealItemIndex === null || $this->selectedFoodId === null) {
            return;
        }

        $normalizedWeight = $this->foodWeightInputService->normalize($this->foodWeight);

        if (! $this->foodWeightInputService->isValid($normalizedWeight)) {
            return;
        }

        $foodId = $this->selectedFoodId;
        $weight = (float) $normalizedWeight;

        if ($this->wouldExceedFoodWeightLimit($foodId, $weight)) {
            return;
        }

        if ($foodId === $originalFoodId) {
            $this->mealItems[$originalMealItemIndex]['weight'] = $weight;
        } else {
            $destinationMealItemIndex = $this->mealItemIndexForFood($foodId);

            if ($destinationMealItemIndex === null) {
                $this->mealItems[$originalMealItemIndex] = [
                    'food_id' => $foodId,
                    'weight' => $weight,
                ];
            } else {
                $this->mealItems[$destinationMealItemIndex]['weight'] += $weight;
                unset($this->mealItems[$originalMealItemIndex]);
                $this->mealItems = array_values($this->mealItems);
            }
        }

        $this->resetFoodModalState();
    }

    private function resetFoodModalState(): void
    {
        $this->isFoodModalOpen = false;
        $this->foodSearch = '';
        $this->selectedFoodId = null;
        $this->foodWeight = '';
        $this->editingMealItemFoodId = null;
    }

    public function selectFood(int $foodId): void
    {
        if (Food::find($foodId) === null) {
            return;
        }

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

    private function canOpenFoodModal(): bool
    {
        return $this->mealItemsCount() < self::MEAL_ITEMS_LIMIT;
    }

    /**
     * @return array<int, array{food_id: int, name: string, weight: float, formatted_weight: string, calories: float, formatted_calories: string, protein: float, formatted_protein: string, carbs: float, formatted_carbs: string, fat: float, formatted_fat: string}>
     */
    private function mealDraftItems(): array
    {
        $foodIds = collect($this->mealItems)
            ->pluck('food_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($foodIds === []) {
            return [];
        }

        $foodsById = Food::query()
            ->whereKey($foodIds)
            ->get()
            ->keyBy('id');

        return collect($this->mealItems)
            ->map(function (array $mealItem) use ($foodsById): ?array {
                if (! isset($mealItem['food_id'], $mealItem['weight'])) {
                    return null;
                }

                /** @var Food|null $food */
                $food = $foodsById->get($mealItem['food_id']);

                if ($food === null) {
                    return null;
                }

                $weight = (float) $mealItem['weight'];
                $calories = $this->nutritionalValuesCalculator->calculateValue((float) $food->calories_per_100g, $weight);
                $protein = $this->nutritionalValuesCalculator->calculateValue((float) $food->protein_per_100g, $weight);
                $carbs = $this->nutritionalValuesCalculator->calculateValue((float) $food->carbs_per_100g, $weight);
                $fat = $this->nutritionalValuesCalculator->calculateValue((float) $food->fat_per_100g, $weight);

                return [
                    'food_id' => $food->id,
                    'name' => $food->localized_name,
                    'weight' => $weight,
                    'formatted_weight' => $this->localizedNutritionalValueFormatter->format($weight),
                    'calories' => $calories,
                    'formatted_calories' => $this->localizedNutritionalValueFormatter->formatDisplayValue($calories),
                    'protein' => $protein,
                    'formatted_protein' => $this->localizedNutritionalValueFormatter->formatDisplayValue($protein),
                    'carbs' => $carbs,
                    'formatted_carbs' => $this->localizedNutritionalValueFormatter->formatDisplayValue($carbs),
                    'fat' => $fat,
                    'formatted_fat' => $this->localizedNutritionalValueFormatter->formatDisplayValue($fat),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function canAddFoodToDraft(): bool
    {
        $weight = $this->foodWeightInputService->normalize($this->foodWeight);

        return $this->selectedFoodId !== null
            && $this->foodWeightInputService->isValid($weight)
            && $this->combinedFoodWeight($this->selectedFoodId, (float) $weight) <= FoodWeightInputService::MAXIMUM_IN_GRAMS;
    }

    private function canUpdateFoodInDraft(): bool
    {
        $weight = $this->foodWeightInputService->normalize($this->foodWeight);

        return $this->editingMealItemFoodId !== null
            && $this->mealItemIndexForFood($this->editingMealItemFoodId) !== null
            && $this->selectedFoodId !== null
            && $this->foodWeightInputService->isValid($weight)
            && ! $this->wouldExceedFoodWeightLimit($this->selectedFoodId, (float) $weight);
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

    private function foodWeightValidationMessage(?Food $selectedFood): ?string
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

        if ($selectedFood !== null
            && $this->wouldExceedFoodWeightLimit($selectedFood->id, (float) $weight)) {
            return __('ui.meals.total_quantity_too_high', [
                'food' => $selectedFood->localized_name,
                'max' => $this->localizedNutritionalValueFormatter->format(FoodWeightInputService::MAXIMUM_IN_GRAMS),
            ]);
        }

        return null;
    }

    private function mealItemIndexForFood(int $foodId): ?int
    {
        foreach ($this->mealItems as $index => $mealItem) {
            if (($mealItem['food_id'] ?? null) === $foodId) {
                return $index;
            }
        }

        return null;
    }

    private function combinedFoodWeight(int $foodId, float $weight): float
    {
        $mealItemIndex = $this->mealItemIndexForFood($foodId);

        if ($mealItemIndex === null) {
            return $weight;
        }

        return (float) $this->mealItems[$mealItemIndex]['weight'] + $weight;
    }

    private function wouldExceedFoodWeightLimit(int $foodId, float $weight): bool
    {
        if ($this->editingMealItemFoodId !== null && $foodId === $this->editingMealItemFoodId) {
            return false;
        }

        return $this->combinedFoodWeight($foodId, $weight) > FoodWeightInputService::MAXIMUM_IN_GRAMS;
    }

    private function formatPreviewValue(float $valuePer100g, float $weight): string
    {
        return $this->localizedNutritionalValueFormatter->formatDisplayValue(
            $this->nutritionalValuesCalculator->calculateValue($valuePer100g, $weight),
        );
    }
}

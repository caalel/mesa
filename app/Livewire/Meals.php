<?php

namespace App\Livewire;

use App\Models\Food;
use App\Services\FoodSearchService;
use App\Services\FoodWeightInputService;
use App\Services\LocalizedNutritionalValueFormatter;
use App\Services\NutritionalValuesCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Meals extends Component
{
    private const MEAL_ITEMS_LIMIT = 10;

    public bool $isMealEditorOpen = false;

    public bool $isFoodModalOpen = false;

    public string $mealName = '';

    public string $foodSearch = '';

    #[Locked]
    public ?int $selectedFoodId = null;

    public string $foodWeight = '';

    #[Locked]
    public ?int $editingMealItemFoodId = null;

    #[Locked]
    public ?int $editingMealId = null;

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
        $mealDraftItems = $this->mealDraftItems();
        $persistedMeals = $this->persistedMeals();

        return view('livewire.meals', [
            'foodSearchResults' => $this->foodSearchResults(),
            'selectedFood' => $selectedFood,
            'selectedFoodNutritionPreview' => $this->selectedFoodNutritionPreview($selectedFood),
            'mealDraftItems' => $mealDraftItems,
            'mealNutritionSummary' => $this->mealNutritionSummary($mealDraftItems),
            'foodWeightValidationMessage' => $this->foodWeightValidationMessage($selectedFood),
            'canAddFoodToDraft' => $this->canAddFoodToDraft(),
            'canUpdateFoodInDraft' => $this->canUpdateFoodInDraft(),
            'canOpenFoodModal' => $this->canOpenFoodModal(),
            'canSubmitMeal' => $this->canSubmitMeal(),
            'hasMealItems' => $this->hasMealItems(),
            'mealItemsCount' => $this->mealItemsCount(),
            'mealItemsLimit' => self::MEAL_ITEMS_LIMIT,
            'hasPersistedMeals' => $persistedMeals !== [],
            'persistedMealsCount' => count($persistedMeals),
            'mealList' => $this->isMealEditorOpen ? [] : $this->mealList($persistedMeals),
        ]);
    }

    public function createMeal(): void
    {
        $this->editingMealId = null;
        $this->isMealEditorOpen = true;
        $this->mealName = '';
        $this->mealItems = [];
    }

    public function editMeal(int $mealId): void
    {
        foreach ($this->persistedMeals() as $meal) {
            if (($meal['id'] ?? null) !== $mealId) {
                continue;
            }

            $this->editingMealId = $mealId;
            $this->mealName = $meal['name'];
            $this->mealItems = $meal['items'];
            $this->isMealEditorOpen = true;

            return;
        }
    }

    public function deleteMeal(int $mealId): void
    {
        $meals = $this->persistedMeals();

        foreach ($meals as $index => $meal) {
            if (($meal['id'] ?? null) !== $mealId) {
                continue;
            }

            unset($meals[$index]);

            session()->put('meals', array_values($meals));

            return;
        }
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
        $this->resetMealEditorState();
    }

    public function submitMeal(): void
    {
        if (! $this->canSubmitMeal()) {
            return;
        }

        $meals = session()->get('meals', []);

        if ($this->editingMealId === null) {
            $lastMealId = collect($meals)->max('id');

            $meals[] = [
                'id' => $lastMealId === null ? 1 : (int) $lastMealId + 1,
                'name' => trim($this->mealName),
                'items' => $this->mealItems,
            ];
        } else {
            $mealWasUpdated = false;

            foreach ($meals as $index => $meal) {
                if (($meal['id'] ?? null) !== $this->editingMealId) {
                    continue;
                }

                $meals[$index] = [
                    'id' => $this->editingMealId,
                    'name' => trim($this->mealName),
                    'items' => $this->mealItems,
                ];
                $mealWasUpdated = true;

                break;
            }

            if (! $mealWasUpdated) {
                return;
            }
        }

        session()->put('meals', $meals);

        $this->resetMealEditorState();
    }

    private function resetMealEditorState(): void
    {
        $this->editingMealId = null;
        $this->isMealEditorOpen = false;
        $this->mealName = '';
        $this->mealItems = [];
        $this->resetFoodModalState();
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

    private function canSubmitMeal(): bool
    {
        $mealNameLength = Str::length(trim($this->mealName));

        return $mealNameLength >= 1
            && $mealNameLength <= 80
            && $this->hasMealItems();
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

        return $this->mealItemsWithNutrition($this->mealItems, $foodsById);
    }

    /**
     * @param  array<int, array{id: int, name: string, items: array<int, array{food_id: int, weight: float}>}>  $meals
     * @return array<int, array{id: int, name: string, items_count: int, nutrition: array{calories: string, protein: string, carbs: string, fat: string}}>
     */
    private function mealList(array $meals): array
    {
        $foodIds = collect($meals)
            ->pluck('items')
            ->flatten(1)
            ->pluck('food_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $foodsById = $foodIds === []
            ? collect()
            : Food::query()->whereKey($foodIds)->get()->keyBy('id');

        return collect($meals)
            ->map(function (array $meal) use ($foodsById): array {
                $items = $this->mealItemsWithNutrition($meal['items'], $foodsById);

                return [
                    'id' => $meal['id'],
                    'name' => $meal['name'],
                    'items_count' => count($meal['items']),
                    'nutrition' => $this->mealNutritionSummary($items),
                ];
            })
            ->all();
    }

    /**
     * @param  array<int, array{food_id: int, weight: float}>  $mealItems
     * @param  Collection<int, Food>  $foodsById
     * @return array<int, array{food_id: int, name: string, weight: float, formatted_weight: string, calories: float, formatted_calories: string, protein: float, formatted_protein: string, carbs: float, formatted_carbs: string, fat: float, formatted_fat: string}>
     */
    private function mealItemsWithNutrition(array $mealItems, Collection $foodsById): array
    {
        return collect($mealItems)
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

    /**
     * @return array<int, array{id: int, name: string, items: array<int, array{food_id: int, weight: float}>}>
     */
    private function persistedMeals(): array
    {
        return session()->get('meals', []);
    }

    /**
     * @param  array<int, array{calories: float, protein: float, carbs: float, fat: float}>  $mealDraftItems
     * @return array{calories: string, protein: string, carbs: string, fat: string}
     */
    private function mealNutritionSummary(array $mealDraftItems): array
    {
        $totals = [
            'calories' => 0.0,
            'protein' => 0.0,
            'carbs' => 0.0,
            'fat' => 0.0,
        ];

        foreach ($mealDraftItems as $mealDraftItem) {
            $totals['calories'] += $mealDraftItem['calories'];
            $totals['protein'] += $mealDraftItem['protein'];
            $totals['carbs'] += $mealDraftItem['carbs'];
            $totals['fat'] += $mealDraftItem['fat'];
        }

        return [
            'calories' => $this->localizedNutritionalValueFormatter->formatDisplayValue($totals['calories']),
            'protein' => $this->localizedNutritionalValueFormatter->formatDisplayValue($totals['protein']),
            'carbs' => $this->localizedNutritionalValueFormatter->formatDisplayValue($totals['carbs']),
            'fat' => $this->localizedNutritionalValueFormatter->formatDisplayValue($totals['fat']),
        ];
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

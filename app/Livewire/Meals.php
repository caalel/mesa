<?php

namespace App\Livewire;

use App\Models\Food;
use App\Services\FoodSearchService;
use App\Services\NutritionalValuesCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Meals extends Component
{
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

    public function boot(
        FoodSearchService $foodSearchService,
        NutritionalValuesCalculator $nutritionalValuesCalculator,
    ): void
    {
        $this->foodSearchService = $foodSearchService;
        $this->nutritionalValuesCalculator = $nutritionalValuesCalculator;
    }

    public function render(): View
    {
        $selectedFood = $this->selectedFood();

        return view('livewire.meals', [
            'foodSearchResults' => $this->foodSearchResults(),
            'selectedFood' => $selectedFood,
            'selectedFoodNutritionPreview' => $this->selectedFoodNutritionPreview($selectedFood),
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

    public function closeFoodModal(): void
    {
        $this->isFoodModalOpen = false;
    }

    public function selectFood(int $foodId): void
    {
        $this->selectedFoodId = $foodId;
        $this->foodWeight = '100';
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

    private function selectedFood(): ?Food
    {
        if ($this->selectedFoodId === null) {
            return null;
        }

        return Food::find($this->selectedFoodId);
    }

    /**
     * @return array{calories: float, protein: float, carbs: float, fat: float}|null
     */
    private function selectedFoodNutritionPreview(?Food $selectedFood): ?array
    {
        if ($selectedFood === null) {
            return null;
        }

        $weight = (float) $this->foodWeight;

        return [
            'calories' => $this->nutritionalValuesCalculator->calculateValue((float) $selectedFood->calories_per_100g, $weight),
            'protein' => $this->nutritionalValuesCalculator->calculateValue((float) $selectedFood->protein_per_100g, $weight),
            'carbs' => $this->nutritionalValuesCalculator->calculateValue((float) $selectedFood->carbs_per_100g, $weight),
            'fat' => $this->nutritionalValuesCalculator->calculateValue((float) $selectedFood->fat_per_100g, $weight),
        ];
    }
}

<?php

namespace App\Livewire;

use App\Services\FoodSearchService;
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

    public function boot(FoodSearchService $foodSearchService): void
    {
        $this->foodSearchService = $foodSearchService;
    }

    public function render(): View
    {
        return view('livewire.meals', [
            'foodSearchResults' => $this->foodSearchResults(),
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
}

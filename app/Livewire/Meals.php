<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Meals extends Component
{
    public bool $isMealEditorOpen = false;

    public bool $isFoodModalOpen = false;

    public ?string $editingMealId = null;

    public string $mealName = '';

    public array $mealItems = [];

    public function render(): View
    {
        return view('livewire.meals');
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

    public function cancelMealEditor(): void
    {
        $this->isMealEditorOpen = false;
        $this->editingMealId = null;
        $this->mealName = '';
        $this->mealItems = [];
    }
}

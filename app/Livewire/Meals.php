<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Meals extends Component
{
    public bool $isMealEditorOpen = false;

    public ?string $editingMealId = null;

    public string $mealName = '';

    public function render(): View
    {
        return view('livewire.meals');
    }

    public function createMeal(): void
    {
        $this->isMealEditorOpen = true;
        $this->editingMealId = null;
        $this->mealName = '';
    }
}

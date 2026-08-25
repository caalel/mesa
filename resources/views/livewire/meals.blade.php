<div class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12" data-testid="meals-page">
    <header class="mb-10 max-w-2xl space-y-3 lg:mb-12">
        <h1 class="text-3xl font-semibold leading-tight text-[var(--color-text-primary)] sm:text-4xl">{{ __('ui.meals.title') }}</h1>
        <p class="text-base leading-7 text-[var(--color-text-secondary)] sm:text-lg">{{ __('ui.meals.subtitle') }}</p>
    </header>

    @if ($isMealEditorOpen)
        <section class="w-full rounded-2xl border border-[var(--color-border)] p-5 sm:p-8" data-testid="meal-editor">
            <div class="flex items-center justify-between gap-5">
                <h2 class="text-xl font-semibold leading-tight text-[var(--color-text-primary)]">{{ __($editingMealId === null ? 'ui.meals.new_meal' : 'ui.meals.editing_meal') }}</h2>
                <span class="text-xs font-medium text-[var(--color-text-secondary)]">{{ __($editingMealId === null ? 'ui.meals.draft_status' : 'ui.meals.editing_status') }}</span>
            </div>

            <div class="mt-7 space-y-2">
                <label class="block text-sm font-medium text-[var(--color-text-secondary)]" for="meal-name">{{ __('ui.meals.name_label') }}</label>
                <input
                    class="w-full rounded-lg border border-[var(--color-border)] px-3 py-2.5 text-[var(--color-text-primary)] focus:border-[var(--color-primary-green)] focus:outline-none focus:ring-1 focus:ring-[var(--color-primary-green)]"
                    id="meal-name"
                    type="text"
                    placeholder="{{ __('ui.meals.name_placeholder') }}"
                    wire:model.live="mealName"
                    data-testid="meal-name"
                >
            </div>

            <div class="mt-8 flex items-center justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-[var(--color-text-primary)]">{{ __('ui.meals.items_title') }}</h3>
                    <p class="mt-1 text-sm text-[var(--color-text-secondary)]">{{ __('ui.meals.items_counter', ['count' => $mealItemsCount, 'limit' => $mealItemsLimit]) }}</p>
                </div>
                @if ($canOpenFoodModal)
                    <button class="cursor-pointer rounded-lg border border-[var(--color-border)] px-4 py-2.5 text-sm font-semibold text-[var(--color-primary-green)] transition-colors hover:bg-[var(--color-light-green)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" wire:click="openFoodModal" data-testid="open-food-modal-enabled">
                        {{ __('ui.meals.add_food') }}
                    </button>
                @else
                    <button class="cursor-not-allowed rounded-lg border border-[var(--color-border)] bg-[var(--color-background)] px-4 py-2.5 text-sm font-semibold text-[var(--color-text-secondary)] opacity-70" type="button" data-testid="open-food-modal-disabled" disabled>
                        {{ __('ui.meals.add_food') }}
                    </button>
                @endif
            </div>

            @if ($hasMealItems)
                <div class="mt-4 divide-y divide-[var(--color-border)] border-y border-[var(--color-border)]" data-testid="meal-items-list">
                    @foreach ($mealDraftItems as $item)
                        <article class="flex items-start justify-between gap-4 py-4" data-testid="meal-item" data-food-id="{{ $item['food_id'] }}">
                            <div class="min-w-0">
                                <h4 class="break-words font-semibold text-[var(--color-text-primary)]">{{ $item['name'] }}</h4>
                                <p class="mt-1 text-sm text-[var(--color-text-secondary)]">
                                    <span class="font-semibold">{{ $item['formatted_weight'] }} g</span>
                                    <span class="mx-2 text-[var(--color-warm-accent)]">•</span>
                                    <span class="font-semibold">{{ $item['formatted_calories'] }} kcal</span>
                                    <span class="hidden md:inline">
                                        <span class="mx-2 text-[var(--color-warm-accent)]">•</span>
                                        <span><span class="font-semibold">{{ $item['formatted_protein'] }} g</span> <span class="lowercase">{{ __('ui.meals.protein') }}</span></span>
                                        <span class="mx-2 text-[var(--color-warm-accent)]">•</span>
                                        <span><span class="font-semibold">{{ $item['formatted_carbs'] }} g</span> <span class="lowercase">{{ __('ui.meals.carbohydrates') }}</span></span>
                                        <span class="mx-2 text-[var(--color-warm-accent)]">•</span>
                                        <span><span class="font-semibold">{{ $item['formatted_fat'] }} g</span> <span class="lowercase">{{ __('ui.meals.fat') }}</span></span>
                                    </span>
                                </p>
                            </div>

                            <div class="flex shrink-0 gap-3 text-xs font-semibold">
                                <button class="cursor-pointer text-[var(--color-primary-green)] transition-colors hover:text-[var(--color-text-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" wire:click="editMealItem({{ $item['food_id'] }})" data-testid="edit-meal-item">
                                    {{ __('ui.meals.edit') }}
                                </button>
                                <button class="cursor-pointer text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-error)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" wire:click="removeMealItem({{ $item['food_id'] }})" data-testid="remove-meal-item">
                                    {{ __('ui.meals.remove') }}
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="mt-4 border-y border-[var(--color-border)]" data-testid="meal-items-empty-state">
                    <p class="py-8 text-center text-sm text-[var(--color-text-secondary)]">{{ __('ui.meals.items_empty_description') }}</p>
                </div>
            @endif

            <section class="mt-7" data-testid="meal-nutrition-summary">
                <h3 class="text-sm font-semibold text-[var(--color-text-primary)]">{{ __('ui.meals.nutrition_summary') }}</h3>
                <div class="mt-3 grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-[var(--color-border)] bg-[var(--color-border)] sm:grid-cols-4">
                    <div class="bg-[var(--color-light-green)] px-4 py-4">
                        <p class="text-2xl font-semibold tracking-[-0.05em] text-[var(--color-text-primary)]">{{ $mealNutritionSummary['calories'] }} kcal</p>
                        <p class="mt-1 text-xs font-medium text-[var(--color-text-secondary)]">{{ __('ui.meals.total_calories') }}</p>
                    </div>
                    <div class="bg-[var(--color-surface)] px-4 py-4">
                        <p class="text-lg font-semibold tracking-[-0.04em] text-[var(--color-text-primary)]">{{ $mealNutritionSummary['protein'] }} g</p>
                        <p class="mt-1 text-xs font-medium text-[var(--color-text-secondary)]">{{ __('ui.meals.protein') }}</p>
                    </div>
                    <div class="bg-[var(--color-surface)] px-4 py-4">
                        <p class="text-lg font-semibold tracking-[-0.04em] text-[var(--color-text-primary)]">{{ $mealNutritionSummary['carbs'] }} g</p>
                        <p class="mt-1 text-xs font-medium text-[var(--color-text-secondary)]">{{ __('ui.meals.carbohydrates') }}</p>
                    </div>
                    <div class="bg-[var(--color-surface)] px-4 py-4">
                        <p class="text-lg font-semibold tracking-[-0.04em] text-[var(--color-text-primary)]">{{ $mealNutritionSummary['fat'] }} g</p>
                        <p class="mt-1 text-xs font-medium text-[var(--color-text-secondary)]">{{ __('ui.meals.fat') }}</p>
                    </div>
                </div>
            </section>

            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button class="cursor-pointer rounded-lg px-5 py-2.5 text-sm font-semibold text-[var(--color-text-secondary)] transition-colors hover:bg-[var(--color-light-green)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" wire:click="cancelMealEditor" data-testid="cancel-meal-editor">
                    {{ __('ui.meals.cancel') }}
                </button>
                @if ($canSubmitMeal)
                    <button class="cursor-pointer rounded-lg bg-[var(--color-primary-green)] px-5 py-2.5 text-sm font-semibold text-white hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" wire:click="submitMeal" data-testid="submit-meal-enabled">
                        {{ __($editingMealId === null ? 'ui.meals.create' : 'ui.meals.save') }}
                    </button>
                @else
                    <button class="cursor-not-allowed rounded-lg border border-[var(--color-border)] bg-[var(--color-background)] px-5 py-2.5 text-sm font-semibold text-[var(--color-text-secondary)] opacity-70" type="button" data-testid="submit-meal-disabled" disabled>
                        {{ __($editingMealId === null ? 'ui.meals.create' : 'ui.meals.save') }}
                    </button>
                @endif
            </div>
        </section>
    @elseif ($hasPersistedMeals)
        <section data-testid="meal-list">
            <div class="flex items-center justify-between gap-4">
                <p class="text-sm text-[var(--color-text-secondary)]">
                    {{ trans_choice('ui.meals.saved_meals_count', $persistedMealsCount, ['count' => $persistedMealsCount]) }}
                </p>
                <button class="cursor-pointer rounded-lg bg-[var(--color-primary-green)] px-5 py-2.5 text-sm font-semibold text-white hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" wire:click="createMeal" data-testid="create-meal">
                    {{ __('ui.meals.create') }}
                </button>
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                @foreach ($mealList as $meal)
                    <article class="rounded-2xl border border-[var(--color-border)] p-5 sm:p-6" data-testid="meal-list-item" data-meal-id="{{ $meal['id'] }}">
                        <div class="min-w-0">
                            <h2 class="break-words text-xl font-semibold leading-tight text-[var(--color-text-primary)]">{{ $meal['name'] }}</h2>
                            <p class="mt-1 text-sm text-[var(--color-text-secondary)]">
                                {{ trans_choice('ui.meals.foods_count', $meal['items_count'], ['count' => $meal['items_count']]) }}
                            </p>
                        </div>

                        <div class="mt-6 flex items-end justify-between gap-3">
                            <span class="text-3xl font-semibold tracking-[-0.05em] text-[var(--color-text-primary)]">{{ $meal['nutrition']['calories'] }} kcal</span>
                            <button class="cursor-pointer shrink-0 text-sm font-semibold text-[var(--color-primary-green)] transition-colors hover:text-[var(--color-text-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" wire:click="editMeal({{ $meal['id'] }})" data-testid="edit-meal">
                                {{ __('ui.meals.edit') }}
                            </button>
                        </div>

                        <p class="mt-4 border-t border-[var(--color-border)] pt-4 text-sm leading-relaxed text-[var(--color-text-secondary)]">
                            <span class="font-semibold text-[var(--color-text-primary)]">{{ $meal['nutrition']['protein'] }} g</span> <span class="lowercase">{{ __('ui.meals.protein') }}</span>
                            <span class="mx-2 text-[var(--color-warm-accent)]">•</span>
                            <span class="font-semibold text-[var(--color-text-primary)]">{{ $meal['nutrition']['carbs'] }} g</span> <span class="lowercase">{{ __('ui.meals.carbohydrates') }}</span>
                            <span class="mx-2 text-[var(--color-warm-accent)]">•</span>
                            <span class="font-semibold text-[var(--color-text-primary)]">{{ $meal['nutrition']['fat'] }} g</span> <span class="lowercase">{{ __('ui.meals.fat') }}</span>
                        </p>
                    </article>
                @endforeach
            </div>
        </section>
    @else
        <section class="flex min-h-64 w-full flex-col items-center justify-center space-y-4 rounded-2xl border border-[var(--color-border)] p-5 text-center sm:min-h-72 sm:p-6 lg:min-h-[20.625rem]" data-testid="meals-empty-state">
            <div class="max-w-2xl space-y-2">
                <h2 class="text-xl font-semibold leading-tight text-[var(--color-text-primary)]">{{ __('ui.meals.empty_title') }}</h2>
                <p class="text-sm leading-6 text-[var(--color-text-secondary)] sm:text-base">{{ __('ui.meals.empty_description') }}</p>
            </div>

            <button class="cursor-pointer rounded-lg bg-[var(--color-primary-green)] px-5 py-2.5 text-sm font-semibold text-white hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" wire:click="createMeal" data-testid="create-meal">
                {{ __('ui.meals.create') }}
            </button>
        </section>
    @endif

    @if ($isFoodModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-[var(--color-text-primary)]/25 p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="food-modal-title" data-testid="food-modal">
            <section class="max-h-[calc(100vh-2rem)] w-full max-w-[38.75rem] overflow-y-auto rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 shadow-xl sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <h2 class="text-xl font-semibold leading-tight text-[var(--color-text-primary)]" id="food-modal-title">
                        {{ __('ui.meals.food_modal_title') }}
                    </h2>
                    <button class="-mr-1 -mt-1 flex h-8 w-8 cursor-pointer items-center justify-center rounded-md text-xl leading-none text-[var(--color-text-secondary)] transition hover:bg-[var(--color-background)] hover:text-[var(--color-text-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" wire:click="cancelFoodModal" aria-label="{{ __('ui.meals.close_food_modal') }}" data-testid="close-food-modal">
                        ×
                    </button>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-[var(--color-text-secondary)]" for="food-search">{{ __('ui.meals.food_search_label') }}</label>
                    <input
                        class="mt-2 h-12 w-full rounded-lg border border-[var(--color-border)] bg-transparent px-4 text-[var(--color-text-primary)] focus:border-[var(--color-primary-green)] focus:outline-none focus:ring-2 focus:ring-[var(--color-light-green)]"
                        id="food-search"
                        type="text"
                        placeholder="{{ __('ui.meals.food_search_placeholder') }}"
                        wire:model.live.debounce.300ms="foodSearch"
                        data-testid="food-search"
                    >
                </div>

                @if ($foodSearchResults->isNotEmpty())
                    <div class="mt-5">
                        <h3 class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--color-text-secondary)]">{{ __('ui.meals.search_results') }}</h3>

                        <ul class="mt-2 overflow-hidden rounded-xl border border-[var(--color-border)]">
                            @foreach ($foodSearchResults as $food)
                                <li class="border-b border-[var(--color-border)] last:border-b-0">
                                    <button
                                        @class([
                                            'flex w-full cursor-pointer items-center justify-between gap-4 px-4 py-3 text-left transition-colors hover:bg-[var(--color-light-green)] focus:bg-[var(--color-light-green)] focus:outline-none',
                                            'bg-[var(--color-light-green)]' => $selectedFoodId === $food->id,
                                        ])
                                        type="button"
                                        wire:click="selectFood({{ $food->id }})"
                                        aria-pressed="{{ $selectedFoodId === $food->id ? 'true' : 'false' }}"
                                        data-testid="select-food-{{ $food->id }}"
                                    >
                                        <span class="min-w-0">
                                            <span class="block break-words text-sm font-medium text-[var(--color-text-primary)]">{{ $food->localized_name }}</span>
                                            <span class="mt-1 block text-sm text-[var(--color-text-secondary)]">{{ (float) $food->calories_per_100g }} kcal / 100 g</span>
                                        </span>

                                        @if ($selectedFoodId === $food->id)
                                            <span class="shrink-0 text-xs font-semibold text-[var(--color-primary-green)]">{{ __('ui.meals.food_selected') }}</span>
                                        @endif
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @elseif (trim($foodSearch) !== '')
                    <div class="mt-5">
                        <h3 class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--color-text-secondary)]">{{ __('ui.meals.search_results') }}</h3>

                        <div class="mt-2 rounded-xl border border-dashed border-[var(--color-border)] px-5 py-9 text-center" data-testid="food-search-empty">
                            <p class="text-sm font-semibold text-[var(--color-text-primary)]">{{ __('ui.meals.food_search_empty') }}</p>
                            <p class="mt-2 text-sm leading-6 text-[var(--color-text-secondary)]">{{ __('ui.meals.food_search_empty_description') }}</p>
                        </div>
                    </div>
                @endif

                @if ($selectedFood !== null)
                    <section
                        class="mt-6 border-t border-[var(--color-border)] pt-6"
                        x-data
                        x-on:selected-food-details-shown.window="$el.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                        data-testid="selected-food-details"
                    >
                        <h3 class="text-base font-semibold text-[var(--color-text-primary)]">{{ $selectedFood->localized_name }}</h3>

                        <label class="mt-5 block text-sm font-medium text-[var(--color-text-secondary)]" for="food-weight">{{ __('ui.meals.food_weight_label') }}</label>
                        <div class="mt-2 flex max-w-[11.25rem] items-center rounded-lg border border-[var(--color-border)] bg-transparent pr-4 focus-within:border-[var(--color-primary-green)] focus-within:ring-2 focus-within:ring-[var(--color-light-green)]">
                            <input
                                class="h-12 min-w-0 flex-1 bg-transparent px-4 text-[var(--color-text-primary)] focus:outline-none"
                                id="food-weight"
                                type="number"
                                wire:model.live.debounce.300ms="foodWeight"
                                data-testid="food-weight"
                            >
                            <span class="text-sm font-medium text-[var(--color-text-secondary)]">g</span>
                        </div>

                        @if ($foodWeightValidationMessage !== null)
                            <p class="mt-2 text-sm text-[var(--color-error)]">{{ $foodWeightValidationMessage }}</p>
                        @endif

                        @if ($selectedFoodNutritionPreview !== null)
                            <div class="mt-5 rounded-xl border border-[var(--color-border)] bg-[var(--color-light-green)] p-4 sm:p-5" data-testid="selected-food-nutrition-preview">
                                <p class="text-sm font-medium text-[var(--color-text-secondary)]">{{ __('ui.meals.nutrition_for_weight', ['weight' => $selectedFoodNutritionPreview['formatted_weight']]) }}</p>
                                <p class="mt-1 text-3xl font-semibold tracking-[-0.05em] text-[var(--color-text-primary)]">{{ $selectedFoodNutritionPreview['calories'] }} kcal</p>

                                <div class="mt-4 grid grid-cols-3 gap-3 border-t border-[var(--color-border)] pt-4 text-sm">
                                    <p>
                                        <span class="block font-semibold text-[var(--color-text-primary)]">{{ $selectedFoodNutritionPreview['protein'] }} g</span>
                                        <span class="text-xs text-[var(--color-text-secondary)]">{{ __('ui.meals.protein') }}</span>
                                    </p>
                                    <p>
                                        <span class="block font-semibold text-[var(--color-text-primary)]">{{ $selectedFoodNutritionPreview['carbs'] }} g</span>
                                        <span class="text-xs text-[var(--color-text-secondary)]">{{ __('ui.meals.carbohydrates') }}</span>
                                    </p>
                                    <p>
                                        <span class="block font-semibold text-[var(--color-text-primary)]">{{ $selectedFoodNutritionPreview['fat'] }} g</span>
                                        <span class="text-xs text-[var(--color-text-secondary)]">{{ __('ui.meals.fat') }}</span>
                                    </p>
                                </div>
                            </div>
                        @endif
                    </section>
                @endif

                <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button class="cursor-pointer rounded-lg px-5 py-3 text-sm font-semibold text-[var(--color-text-secondary)] transition hover:bg-[var(--color-light-green)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" wire:click="cancelFoodModal" data-testid="cancel-food-modal">
                        {{ __('ui.meals.cancel') }}
                    </button>
                    @if ($editingMealItemFoodId !== null)
                        @if ($canUpdateFoodInDraft)
                            <button class="cursor-pointer rounded-lg bg-[var(--color-primary-green)] px-5 py-3 text-sm font-semibold text-white transition hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" wire:click="updateFoodInDraft" data-testid="update-food-in-draft-enabled">
                                {{ __('ui.meals.save') }}
                            </button>
                        @else
                            <button class="cursor-not-allowed rounded-lg border border-[var(--color-border)] bg-[var(--color-background)] px-5 py-3 text-sm font-semibold text-[var(--color-text-secondary)] opacity-70" type="button" data-testid="update-food-in-draft-disabled" disabled>
                                {{ __('ui.meals.save') }}
                            </button>
                        @endif
                    @elseif ($canAddFoodToDraft)
                        <button class="cursor-pointer rounded-lg bg-[var(--color-primary-green)] px-5 py-3 text-sm font-semibold text-white transition hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" wire:click="addFoodToDraft" data-testid="add-food-to-draft-enabled">
                            {{ __('ui.meals.add_food_to_draft') }}
                        </button>
                    @else
                        <button class="cursor-not-allowed rounded-lg border border-[var(--color-border)] bg-[var(--color-background)] px-5 py-3 text-sm font-semibold text-[var(--color-text-secondary)] opacity-70" type="button" data-testid="add-food-to-draft-disabled" disabled>
                            {{ __('ui.meals.add_food_to_draft') }}
                        </button>
                    @endif
                </div>
            </section>
        </div>
    @endif
</div>

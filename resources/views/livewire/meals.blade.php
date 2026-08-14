<div class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12" data-testid="meals-page">
    <header class="mb-10 max-w-2xl space-y-3 lg:mb-12">
        <h1 class="text-3xl font-semibold leading-tight text-[var(--color-text-primary)] sm:text-4xl">{{ __('ui.meals.title') }}</h1>
        <p class="text-base leading-7 text-[var(--color-text-secondary)] sm:text-lg">{{ __('ui.meals.subtitle') }}</p>
    </header>

    @if ($isMealEditorOpen)
        <section class="w-full space-y-6 rounded-2xl border border-[var(--color-border)] p-5 sm:p-6" data-testid="meal-editor">
            <h2 class="text-xl font-semibold leading-tight text-[var(--color-text-primary)]">{{ __('ui.meals.new_meal') }}</h2>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-[var(--color-text-secondary)]" for="meal-name">{{ __('ui.meals.name_label') }}</label>
                <input
                    class="w-full rounded-lg border border-[var(--color-border)] px-3 py-2.5 text-[var(--color-text-primary)] focus:border-[var(--color-primary-green)] focus:outline-none focus:ring-1 focus:ring-[var(--color-primary-green)]"
                    id="meal-name"
                    type="text"
                    placeholder="{{ __('ui.meals.name_placeholder') }}"
                    wire:model="mealName"
                    data-testid="meal-name"
                >
            </div>

            <div class="flex justify-end">
                <button class="cursor-pointer rounded-lg border border-[var(--color-border)] px-4 py-2.5 text-sm font-semibold text-[var(--color-primary-green)] transition-colors hover:bg-[var(--color-light-green)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" wire:click="openFoodModal" data-testid="open-food-modal">
                    {{ __('ui.meals.add_food') }}
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button class="cursor-pointer rounded-lg bg-[var(--color-primary-green)] px-5 py-2.5 text-sm font-semibold text-white hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" data-testid="submit-meal">
                    {{ __('ui.meals.create') }}
                </button>
                <button class="cursor-pointer rounded-lg px-4 py-2.5 text-sm font-medium text-[var(--color-text-secondary)] transition-colors hover:bg-[var(--color-light-green)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" wire:click="cancelMealEditor" data-testid="cancel-meal-editor">
                    {{ __('ui.meals.cancel') }}
                </button>
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
                    <button class="-mr-1 -mt-1 flex h-8 w-8 cursor-pointer items-center justify-center rounded-md text-xl leading-none text-[var(--color-text-secondary)] transition hover:bg-[var(--color-background)] hover:text-[var(--color-text-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" wire:click="closeFoodModal" aria-label="{{ __('ui.meals.close_food_modal') }}" data-testid="close-food-modal">
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
            </section>
        </div>
    @endif
</div>

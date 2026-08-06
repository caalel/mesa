<div class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12" data-testid="meals-page">
    <header class="mb-10 max-w-2xl space-y-3 lg:mb-12">
        <h1 class="text-3xl font-semibold leading-tight text-[var(--color-text-primary)] sm:text-4xl">{{ __('ui.meals.title') }}</h1>
        <p class="text-base leading-7 text-[var(--color-text-secondary)] sm:text-lg">{{ __('ui.meals.subtitle') }}</p>
    </header>

    @if ($isMealEditorOpen)
        <section class="max-w-2xl space-y-6 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 sm:p-6" data-testid="meal-editor">
            <h2 class="text-xl font-semibold leading-tight text-[var(--color-text-primary)]">{{ __('ui.meals.new_meal') }}</h2>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-[var(--color-text-secondary)]" for="meal-name">{{ __('ui.meals.name_label') }}</label>
                <input
                    class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] px-3 py-2.5 text-[var(--color-text-primary)] focus:border-[var(--color-primary-green)] focus:outline-none focus:ring-1 focus:ring-[var(--color-primary-green)]"
                    id="meal-name"
                    type="text"
                    wire:model="mealName"
                    data-testid="meal-name"
                >
            </div>

            <button class="rounded-lg bg-[var(--color-primary-green)] px-5 py-2.5 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" data-testid="submit-meal">
                {{ __('ui.meals.create') }}
            </button>
        </section>
    @else
        <section class="max-w-2xl space-y-4 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 sm:p-6" data-testid="meals-empty-state">
            <div class="space-y-2">
                <h2 class="text-xl font-semibold leading-tight text-[var(--color-text-primary)]">{{ __('ui.meals.empty_title') }}</h2>
                <p class="text-sm leading-6 text-[var(--color-text-secondary)] sm:text-base">{{ __('ui.meals.empty_description') }}</p>
            </div>

            <button class="rounded-lg bg-[var(--color-primary-green)] px-5 py-2.5 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2" type="button" wire:click="createMeal" data-testid="create-meal">
                {{ __('ui.meals.create') }}
            </button>
        </section>
    @endif
</div>

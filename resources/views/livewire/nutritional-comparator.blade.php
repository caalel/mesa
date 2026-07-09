<div class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
    <header class="mb-10 max-w-2xl space-y-3 lg:mb-12">
        <h1 class="text-3xl font-semibold leading-tight text-[var(--color-text-primary)] sm:text-4xl">{{ __('ui.compare.title') }}</h1>
        <p class="text-base leading-7 text-[var(--color-text-secondary)] sm:text-lg">{{ __('ui.compare.subtitle') }}</p>
    </header>

    <div class="space-y-8">
        <div class="grid gap-6 lg:grid-cols-2 lg:items-start lg:gap-8">
            <section class="min-w-0 space-y-6 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 sm:p-6">
                <h2 class="text-lg font-semibold leading-6 text-[var(--color-text-primary)]">{{ __('ui.compare.food_a_section') }}</h2>

                @if ($selectedFoodA)
                    <x-compare-selected-food :name="$selectedFoodA->name_pt" wire:click="changeFoodA" />
                @else
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-[var(--color-text-secondary)]" for="food-a-search">{{ __('ui.compare.search_food') }}</label>
                        <input class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] px-3 py-2.5 text-[var(--color-text-primary)] focus:border-[var(--color-primary-green)] focus:outline-none focus:ring-1 focus:ring-[var(--color-primary-green)] disabled:cursor-not-allowed disabled:bg-[var(--color-background)] disabled:text-[var(--color-text-secondary)] disabled:opacity-70" id="food-a-search" type="text" wire:model.live.debounce.300ms="foodASearch">
                        <p class="text-sm text-[var(--color-text-secondary)]">{{ __('ui.compare.search_help') }}</p>

                        <ul class="space-y-2">
                            @foreach ($foodAResults as $food)
                                <x-compare-search-result-item :food="$food" select-action="selectFoodA" />
                            @endforeach
                        </ul>

                        @if ($foodAHasNoResults)
                            <p class="text-sm text-[var(--color-text-secondary)]">{{ __('ui.compare.no_foods_found') }}</p>
                        @endif
                    </div>
                @endif

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-[var(--color-text-secondary)]" for="food-a-quantity">{{ __('ui.compare.quantity_label') }}</label>
                    <div class="flex items-center gap-2">
                        <input
                            class="min-w-0 flex-1 rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] px-3 py-2.5 text-[var(--color-text-primary)] focus:border-[var(--color-primary-green)] focus:outline-none focus:ring-1 focus:ring-[var(--color-primary-green)] disabled:cursor-not-allowed disabled:bg-[var(--color-background)] disabled:text-[var(--color-text-secondary)] disabled:opacity-70"
                            id="food-a-quantity"
                            type="number"
                            placeholder="{{ __('ui.compare.quantity_placeholder') }}"
                            wire:model.live="foodAWeight"
                            @disabled(! $selectedFoodA)
                        >
                        <span class="shrink-0 text-sm font-medium text-[var(--color-text-secondary)]">{{ __('ui.compare.grams_unit') }}</span>
                    </div>

                    @if ($foodAWeightExceedsMaximum)
                        <p class="text-sm text-[var(--color-error)]">{{ __('ui.compare.quantity_too_high', ['max' => $formattedFoodAWeightMaximum]) }}</p>
                    @endif
                </div>

                @if ($foodASummary)
                    <div class="space-y-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-background)] p-4" data-testid="food-a-summary">
                        <p class="break-words text-sm font-medium leading-5 text-[var(--color-text-primary)]">{{ $foodASummary['food']->name_pt }}</p>
                        <p class="text-sm text-[var(--color-text-secondary)]">{{ $foodASummary['formatted_weight'] }} {{ __('ui.compare.grams_unit') }}</p>
                        <p class="text-lg font-semibold leading-6 text-[var(--color-primary-green)]">{{ $foodASummary['formatted_calories'] }} kcal</p>
                    </div>
                @endif
            </section>

            <section class="min-w-0 space-y-6 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 sm:p-6">
                <h2 class="text-lg font-semibold leading-6 text-[var(--color-text-primary)]">{{ __('ui.compare.food_b_section') }}</h2>

                @if ($selectedFoodB)
                    <x-compare-selected-food :name="$selectedFoodB->name_pt" wire:click="changeFoodB" />
                @else
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-[var(--color-text-secondary)]" for="food-b-search">{{ __('ui.compare.search_food') }}</label>
                        <input class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] px-3 py-2.5 text-[var(--color-text-primary)] focus:border-[var(--color-primary-green)] focus:outline-none focus:ring-1 focus:ring-[var(--color-primary-green)] disabled:cursor-not-allowed disabled:bg-[var(--color-background)] disabled:text-[var(--color-text-secondary)] disabled:opacity-70" id="food-b-search" type="text" wire:model.live.debounce.300ms="foodBSearch">
                        <p class="text-sm text-[var(--color-text-secondary)]">{{ __('ui.compare.search_help') }}</p>

                        <ul class="space-y-2">
                            @foreach ($foodBResults as $food)
                                <x-compare-search-result-item :food="$food" select-action="selectFoodB" />
                            @endforeach
                        </ul>

                        @if ($foodBHasNoResults)
                            <p class="text-sm text-[var(--color-text-secondary)]">{{ __('ui.compare.no_foods_found') }}</p>
                        @endif
                    </div>
                @endif
            </section>
        </div>

        <div class="flex justify-center">
            @if ($canCompare)
                <button class="w-full cursor-pointer rounded-lg bg-[var(--color-primary-green)] px-6 py-3 text-sm font-semibold text-white hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-green)] focus:ring-offset-2 sm:w-auto sm:min-w-48" type="button" wire:click="compare" data-testid="compare-button-enabled">
                    {{ __('ui.compare.submit') }}
                </button>
            @else
                <button class="w-full cursor-not-allowed rounded-lg border border-[var(--color-border)] bg-[var(--color-background)] px-6 py-3 text-sm font-semibold text-[var(--color-text-secondary)] opacity-70 sm:w-auto sm:min-w-48" type="button" data-testid="compare-button-disabled" disabled>
                    {{ __('ui.compare.submit') }}
                </button>
            @endif
        </div>

        @if ($comparisonResult)
            <section
                class="w-full space-y-4 rounded-2xl border border-[var(--color-border)] border-l-4 border-l-[var(--color-warm-accent)] bg-[var(--color-surface)] p-5 sm:p-6"
                x-data
                x-on:comparison-result-shown.window="$el.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                data-testid="comparison-result"
            >
                <p class="break-words text-2xl font-semibold leading-tight text-[var(--color-text-primary)] sm:text-3xl">
                    @if ($comparisonResult['food_b_weight_is_less_than_minimum'])
                        {{ __('ui.compare.calorie_equivalence_less_than', [
                            'foodAWeight' => $comparisonResult['food_a_weight'],
                            'foodAName' => $comparisonResult['food_a_name'],
                            'foodBWeight' => $comparisonResult['food_b_weight'],
                            'foodBName' => $comparisonResult['food_b_name'],
                        ]) }}
                    @else
                        {{ __('ui.compare.calorie_equivalence', [
                            'foodAWeight' => $comparisonResult['food_a_weight'],
                            'foodAName' => $comparisonResult['food_a_name'],
                            'foodBWeight' => $comparisonResult['food_b_weight'],
                            'foodBName' => $comparisonResult['food_b_name'],
                        ]) }}
                    @endif
                </p>
                <p class="break-words border-t border-[var(--color-border)] pt-4 text-sm leading-6 text-[var(--color-text-secondary)] sm:text-base">
                    @if ($comparisonResult['food_b_weight_is_less_than_minimum'])
                        {{ __('ui.compare.calorie_equivalence_less_than_description', [
                            'foodAWeight' => $comparisonResult['food_a_weight'],
                            'foodAName' => $comparisonResult['food_a_name'],
                            'foodBWeight' => $comparisonResult['food_b_weight'],
                            'foodBName' => $comparisonResult['food_b_name'],
                        ]) }}
                    @else
                        {{ __('ui.compare.calorie_equivalence_description', [
                            'foodAWeight' => $comparisonResult['food_a_weight'],
                            'foodAName' => $comparisonResult['food_a_name'],
                            'foodBWeight' => $comparisonResult['food_b_weight'],
                            'foodBName' => $comparisonResult['food_b_name'],
                        ]) }}
                    @endif
                </p>
            </section>
        @endif
    </div>
</div>

<div class="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6 sm:py-12 lg:px-8 lg:py-14">
    <header class="mb-12 max-w-2xl space-y-3">
        <h1 class="text-3xl font-semibold leading-tight text-[var(--color-text-primary)] sm:text-4xl">{{ __('ui.compare.title') }}</h1>
        <p class="text-base leading-7 text-[var(--color-text-secondary)] sm:text-lg">{{ __('ui.compare.subtitle') }}</p>
    </header>

    <div class="space-y-7">
        <section class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 sm:p-6" data-testid="comparison-nutrient-selector">
            <p class="text-sm font-semibold text-[var(--color-text-primary)]">{{ __('ui.compare.compare_by') }}</p>
            <div class="mt-4 grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-[var(--color-border)] bg-[var(--color-border)] sm:inline-flex sm:rounded-lg">
                @foreach ($comparisonNutrients as $nutrient)
                    <button
                        class="cursor-pointer px-4 py-3 text-sm font-semibold transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--color-primary-green)] {{ $selectedNutrient === $nutrient ? 'bg-[var(--color-light-green)] text-[var(--color-primary-green)]' : 'bg-[var(--color-surface)] text-[var(--color-text-secondary)] hover:bg-[var(--color-background)]' }}"
                        type="button"
                        wire:click="$set('selectedNutrient', '{{ $nutrient->value }}')"
                        aria-pressed="{{ $selectedNutrient === $nutrient ? 'true' : 'false' }}"
                        data-testid="comparison-nutrient-{{ $nutrient->value }}"
                    >
                        {{ __('ui.compare.nutrients.'.$nutrient->value) }}
                    </button>
                @endforeach
            </div>
        </section>

        <div class="grid items-start gap-5 lg:grid-cols-2">
            <section class="min-w-0 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 sm:p-6">
                <h2 class="text-lg font-semibold leading-6 text-[var(--color-text-primary)]">{{ __('ui.compare.food_a_section') }}</h2>

                @if ($selectedFoodA)
                    <x-compare-selected-food :name="$selectedFoodA->localized_name" wire:click="changeFoodA" />
                    @if ($foodAHasUnavailableSelectedNutrient)
                        <p class="mt-3 text-sm text-[var(--color-error)] first-letter:uppercase" data-testid="food-a-nutrient-error" role="alert">
                            {{ __('ui.compare.nutrient_data_unavailable', ['nutrient' => str(__('ui.compare.nutrients.'.$selectedNutrient->value))->lower()]) }}
                        </p>
                    @endif
                @else
                    <div>
                        <label class="mt-6 block text-sm font-medium text-[var(--color-text-secondary)]" for="food-a-search">{{ __('ui.compare.search_food') }}</label>
                        <input class="mt-2 h-12 w-full rounded-lg border border-[var(--color-border)] bg-transparent px-4 text-[var(--color-text-primary)] focus:border-[var(--color-primary-green)] focus:outline-none focus:ring-2 focus:ring-[var(--color-light-green)]" id="food-a-search" type="text" placeholder="{{ __('ui.compare.search_placeholder') }}" wire:model.live.debounce.300ms="foodASearch">

                        @if ($foodAResults->isNotEmpty())
                            <ul class="mt-3 overflow-hidden rounded-xl border border-[var(--color-border)]">
                                @foreach ($foodAResults as $food)
                                    <x-compare-search-result-item :food="$food" select-action="selectFoodA" />
                                @endforeach
                            </ul>
                        @endif

                        @if ($foodAHasNoResults)
                            <p class="mt-3 text-sm text-[var(--color-text-secondary)]">{{ __('ui.compare.no_foods_found') }}</p>
                        @endif
                    </div>
                @endif

                @if ($selectedFoodA)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-[var(--color-text-secondary)]" for="food-a-quantity">{{ __('ui.compare.quantity_label') }}</label>
                        <div class="mt-2 flex items-center rounded-lg border border-[var(--color-border)] pr-4 focus-within:border-[var(--color-primary-green)] focus-within:ring-2 focus-within:ring-[var(--color-light-green)]">
                            <input
                                class="h-12 min-w-0 flex-1 bg-transparent px-4 text-[var(--color-text-primary)] focus:outline-none disabled:cursor-not-allowed disabled:bg-[var(--color-background)] disabled:text-[var(--color-text-secondary)] disabled:opacity-70"
                                id="food-a-quantity"
                                type="number"
                                placeholder="{{ __('ui.compare.quantity_placeholder') }}"
                                wire:model.live.debounce.300ms="foodAWeight"
                                @disabled($foodAHasUnavailableSelectedNutrient)
                            >
                            <span class="shrink-0 text-sm font-medium text-[var(--color-text-secondary)]">{{ __('ui.compare.grams_unit') }}</span>
                        </div>

                        @if ($foodAWeightValidationMessage)
                            <p class="mt-2 text-sm text-[var(--color-error)]">{{ $foodAWeightValidationMessage }}</p>
                        @endif
                    </div>

                    @if ($foodASummary)
                        <div class="mt-6 border-t border-[var(--color-border)] pt-5">
                            <x-compare-nutritional-summary :summary="$foodASummary" test-id="food-a-summary" />
                        </div>
                    @endif
                @endif
            </section>

            <section class="min-w-0 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 sm:p-6">
                <h2 class="text-lg font-semibold leading-6 text-[var(--color-text-primary)]">{{ __('ui.compare.food_b_section') }}</h2>

                @if ($selectedFoodB)
                    <x-compare-selected-food :name="$selectedFoodB->localized_name" wire:click="changeFoodB" />
                    @if ($foodBHasUnavailableSelectedNutrient)
                        <p class="mt-3 text-sm text-[var(--color-error)] first-letter:uppercase" data-testid="food-b-nutrient-error" role="alert">
                            {{ __('ui.compare.nutrient_data_unavailable', ['nutrient' => str(__('ui.compare.nutrients.'.$selectedNutrient->value))->lower()]) }}
                        </p>
                    @endif

                    @if ($foodBSummary)
                        <div class="mt-6 border-t border-[var(--color-border)] pt-5">
                            <x-compare-nutritional-summary :summary="$foodBSummary" test-id="food-b-summary" />
                        </div>
                    @endif
                @else
                    <div>
                        <label class="mt-6 block text-sm font-medium text-[var(--color-text-secondary)]" for="food-b-search">{{ __('ui.compare.search_food') }}</label>
                        <input class="mt-2 h-12 w-full rounded-lg border border-[var(--color-border)] bg-transparent px-4 text-[var(--color-text-primary)] focus:border-[var(--color-primary-green)] focus:outline-none focus:ring-2 focus:ring-[var(--color-light-green)]" id="food-b-search" type="text" placeholder="{{ __('ui.compare.search_placeholder') }}" wire:model.live.debounce.300ms="foodBSearch">

                        @if ($foodBResults->isNotEmpty())
                            <ul class="mt-3 overflow-hidden rounded-xl border border-[var(--color-border)]">
                                @foreach ($foodBResults as $food)
                                    <x-compare-search-result-item :food="$food" select-action="selectFoodB" />
                                @endforeach
                            </ul>
                        @endif

                        @if ($foodBHasNoResults)
                            <p class="mt-3 text-sm text-[var(--color-text-secondary)]">{{ __('ui.compare.no_foods_found') }}</p>
                        @endif
                    </div>
                @endif
            </section>
        </div>

        @if ($comparisonResult)
            <section
                class="w-full rounded-2xl border border-[var(--color-border)] border-l-[3px] border-l-[var(--color-warm-accent)] bg-[var(--color-surface)] p-5 sm:p-6"
                data-testid="comparison-result"
                x-data
                x-on:comparison-result-available.window="$el.scrollIntoView({ behavior: 'smooth', block: 'start' })"
            >
                <p class="text-sm font-semibold text-[var(--color-primary-green)]">{{ __('ui.compare.equivalence_by', ['nutrient' => __('ui.compare.nutrients.'.$selectedNutrient->value)]) }}</p>
                <p class="mt-2 break-words text-2xl font-semibold leading-tight text-[var(--color-text-primary)] sm:text-3xl">
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
                <p class="mt-5 break-words border-t border-[var(--color-border)] pt-5 text-sm leading-6 text-[var(--color-text-secondary)] sm:text-base">
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

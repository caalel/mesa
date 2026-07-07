<div>
    <h1>{{ __('ui.compare.title') }}</h1>
    <p>{{ __('ui.compare.subtitle') }}</p>

    <section>
        <h2>{{ __('ui.compare.food_a_section') }}</h2>

        @if ($selectedFoodA)
            <p>{{ $selectedFoodA->name_pt }}</p>
            <button type="button" wire:click="changeFoodA">{{ __('ui.compare.change_food') }}</button>
        @else
            <label for="food-a-search">{{ __('ui.compare.search_food') }}</label>
            <input id="food-a-search" type="text" wire:model.live.debounce.300ms="foodASearch">

            <ul>
                @foreach ($foodAResults as $food)
                    <li>
                        <button type="button" wire:click="selectFoodA({{ $food->id }})">
                            {{ $food->name_pt }}
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif

        <label for="food-a-quantity">{{ __('ui.compare.quantity_label') }}</label>
        <input
            id="food-a-quantity"
            type="number"
            wire:model.live="foodAWeight"
            @disabled(! $selectedFoodA)
        >
        <span>{{ __('ui.compare.grams_unit') }}</span>

        @if ($foodASummary)
            <div data-testid="food-a-summary">
                <p>{{ $foodASummary['food']->name_pt }}</p>
                <p>{{ $foodASummary['weight'] }} {{ __('ui.compare.grams_unit') }}</p>
                <p>{{ $foodASummary['calories'] }} kcal</p>
            </div>
        @endif
    </section>

    <section>
        <h2>{{ __('ui.compare.food_b_section') }}</h2>

        @if ($selectedFoodB)
            <p>{{ $selectedFoodB->name_pt }}</p>
            <button type="button" wire:click="changeFoodB">{{ __('ui.compare.change_food') }}</button>
        @else
            <label for="food-b-search">{{ __('ui.compare.search_food') }}</label>
            <input id="food-b-search" type="text" wire:model.live.debounce.300ms="foodBSearch">

            <ul>
                @foreach ($foodBResults as $food)
                    <li>
                        <button type="button" wire:click="selectFoodB({{ $food->id }})">
                            {{ $food->name_pt }}
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    @if ($canCompare)
        <button type="button" wire:click="compare" data-testid="compare-button-enabled">
            {{ __('ui.compare.submit') }}
        </button>
    @else
        <button type="button" data-testid="compare-button-disabled" disabled>
            {{ __('ui.compare.submit') }}
        </button>
    @endif

    @if ($comparisonResult)
        <section data-testid="comparison-result">
            <p>
                {{ __('ui.compare.calorie_equivalence', [
                    'foodAWeight' => $comparisonResult['food_a_weight'],
                    'foodAName' => $comparisonResult['food_a_name'],
                    'foodBWeight' => $comparisonResult['food_b_weight'],
                    'foodBName' => $comparisonResult['food_b_name'],
                ]) }}
            </p>
            <p>
                {{ __('ui.compare.calorie_equivalence_description', [
                    'foodAWeight' => $comparisonResult['food_a_weight'],
                    'foodAName' => $comparisonResult['food_a_name'],
                    'foodBWeight' => $comparisonResult['food_b_weight'],
                    'foodBName' => $comparisonResult['food_b_name'],
                ]) }}
            </p>
        </section>
    @endif
</div>

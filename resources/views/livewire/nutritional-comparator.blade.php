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

    <button type="button" disabled>{{ __('ui.compare.submit') }}</button>
</div>

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
        <input id="food-a-quantity" type="number" disabled>
        <span>{{ __('ui.compare.grams_unit') }}</span>
    </section>

    <section>
        <h2>{{ __('ui.compare.food_b_section') }}</h2>

        <label for="food-b-search">{{ __('ui.compare.search_food') }}</label>
        <input id="food-b-search" type="text" disabled>
    </section>

    <button type="button" disabled>{{ __('ui.compare.submit') }}</button>
</div>

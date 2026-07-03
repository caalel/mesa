<div>
    <h1>{{ __('ui.compare.title') }}</h1>
    <p>{{ __('ui.compare.subtitle') }}</p>

    <input type="text" wire:model.live.debounce.300ms="foodASearch">

    <ul>
        @foreach ($foodAResults as $food)
            <li>{{ $food->name_pt }}</li>
        @endforeach
    </ul>
</div>

@component('layouts.app')
    <main data-testid="homepage">
        <a href="{{ route('comparator') }}" data-testid="open-comparator">
            {{ __('ui.navigation.comparator') }}
        </a>
    </main>
@endcomponent

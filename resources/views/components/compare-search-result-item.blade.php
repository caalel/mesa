@props(['food', 'selectAction'])

<li class="border-b border-[var(--color-border)] last:border-b-0">
    <button class="w-full cursor-pointer break-words bg-transparent px-4 py-3 text-left text-sm font-medium text-[var(--color-text-primary)] transition hover:bg-[var(--color-light-green)] focus:bg-[var(--color-light-green)] focus:outline-none" type="button" wire:click="{{ $selectAction }}({{ $food->id }})">
        {{ $food->localized_name }}
    </button>
</li>

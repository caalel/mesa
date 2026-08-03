@props(['food', 'selectAction'])

<li>
    <button class="w-full break-words rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] px-3 py-2 text-left text-sm text-[var(--color-text-primary)] hover:bg-[var(--color-light-green)]" type="button" wire:click="{{ $selectAction }}({{ $food->id }})">
        {{ $food->localized_name }}
    </button>
</li>

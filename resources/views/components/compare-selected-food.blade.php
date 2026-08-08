@props(['name'])

<div class="mt-5 flex min-w-0 items-center justify-between gap-4 rounded-xl bg-[var(--color-light-green)] px-4 py-4">
    <p class="min-w-0 break-words text-sm font-semibold leading-5 text-[var(--color-text-primary)]">{{ $name }}</p>
    <button
        type="button"
        {{ $attributes->merge(['class' => 'shrink-0 cursor-pointer rounded-md border border-[var(--color-border)] bg-[var(--color-surface)] px-3 py-1.5 text-xs font-semibold text-[var(--color-primary-green)] transition hover:bg-[var(--color-background)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-green)]']) }}
    >
        {{ __('ui.compare.change_food') }}
    </button>
</div>

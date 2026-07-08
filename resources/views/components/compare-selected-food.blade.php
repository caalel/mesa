@props(['name'])

<div class="flex min-w-0 items-start justify-between gap-4 rounded-xl border border-[var(--color-border)] bg-[var(--color-light-green)] p-4">
    <p class="min-w-0 break-words text-base font-semibold leading-6 text-[var(--color-text-primary)]">{{ $name }}</p>
    <button
        type="button"
        {{ $attributes->merge(['class' => 'shrink-0 rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] px-3 py-1.5 text-sm font-medium text-[var(--color-primary-green)] hover:border-[var(--color-primary-green)]']) }}
    >
        {{ __('ui.compare.change_food') }}
    </button>
</div>

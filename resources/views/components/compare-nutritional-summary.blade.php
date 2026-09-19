@props(['summary', 'testId'])

<div data-testid="{{ $testId }}">
    <p class="text-xs font-semibold text-[var(--color-warm-accent)]">{{ __('ui.compare.summary_for_weight', ['weight' => $summary['formatted_weight'], 'unit' => __('ui.compare.grams_unit')]) }}</p>

    <div class="mt-2 grid grid-cols-2 gap-x-6 gap-y-5 rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] px-3 py-3 sm:px-4">
        <div>
            <p class="text-sm font-semibold leading-5 text-[var(--color-text-primary)]" data-testid="{{ $testId }}-calories-value">{{ $summary['formatted_calories'] }} {{ __('ui.compare.calories_unit') }}</p>
            <p class="mt-0.5 text-xs text-[var(--color-text-secondary)]">{{ __('ui.compare.nutrients.calories') }}</p>
        </div>
        <div>
            <p class="text-sm font-semibold leading-5 text-[var(--color-text-primary)]" data-testid="{{ $testId }}-protein-value">{{ $summary['formatted_protein'] }} {{ __('ui.compare.grams_unit') }}</p>
            <p class="mt-0.5 text-xs text-[var(--color-text-secondary)]">{{ __('ui.compare.nutrients.protein') }}</p>
        </div>
        <div>
            <p class="text-sm font-semibold leading-5 text-[var(--color-text-primary)]" data-testid="{{ $testId }}-carbs-value">{{ $summary['formatted_carbs'] }} {{ __('ui.compare.grams_unit') }}</p>
            <p class="mt-0.5 text-xs text-[var(--color-text-secondary)]">{{ __('ui.compare.nutrients.carbohydrates') }}</p>
        </div>
        <div>
            <p class="text-sm font-semibold leading-5 text-[var(--color-text-primary)]" data-testid="{{ $testId }}-fat-value">{{ $summary['formatted_fat'] }} {{ __('ui.compare.grams_unit') }}</p>
            <p class="mt-0.5 text-xs text-[var(--color-text-secondary)]">{{ __('ui.compare.nutrients.fat') }}</p>
        </div>
    </div>
</div>

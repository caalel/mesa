<?php

use App\Services\LocalizedNutritionalValueFormatter;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

uses(TestCase::class);

it('formats an integer using the active locale', function () {
    App::setLocale('pt_BR');

    expect((new LocalizedNutritionalValueFormatter())->format(12))->toBe('12');
});

it('formats decimals without unnecessary trailing zeroes', function () {
    App::setLocale('pt_BR');

    expect((new LocalizedNutritionalValueFormatter())->format(12.50))->toBe('12,5');
});

it('formats thousands using pt-BR separators', function () {
    App::setLocale('pt_BR');

    expect((new LocalizedNutritionalValueFormatter())->format(1234.56))->toBe('1.234,56');
});

it('formats thousands using English separators', function () {
    App::setLocale('en');

    expect((new LocalizedNutritionalValueFormatter())->format(1234.56))->toBe('1,234.56');
});

it('formats a positive value below the display minimum using pt-BR separators', function () {
    App::setLocale('pt_BR');

    expect((new LocalizedNutritionalValueFormatter())->formatDisplayValue(0.009))->toBe('< 0,01');
});

it('formats a positive value below the display minimum using English separators', function () {
    App::setLocale('en');

    expect((new LocalizedNutritionalValueFormatter())->formatDisplayValue(0.009))->toBe('< 0.01');
});

it('does not prefix zero with less than', function () {
    App::setLocale('pt_BR');

    expect((new LocalizedNutritionalValueFormatter())->formatDisplayValue(0))->toBe('0');
});

it('does not prefix a negative value with less than', function () {
    App::setLocale('pt_BR');

    expect((new LocalizedNutritionalValueFormatter())->formatDisplayValue(-0.009))->toBe('-0,01');
});

it('does not prefix the display minimum with less than', function () {
    App::setLocale('pt_BR');

    expect((new LocalizedNutritionalValueFormatter())->formatDisplayValue(0.01))->toBe('0,01');
});

it('detects only positive values strictly below the display minimum', function () {
    $formatter = new LocalizedNutritionalValueFormatter();

    expect($formatter->isPositiveValueBelowDisplayMinimum(0))->toBeFalse();
    expect($formatter->isPositiveValueBelowDisplayMinimum(-0.009))->toBeFalse();
    expect($formatter->isPositiveValueBelowDisplayMinimum(0.01))->toBeFalse();
    expect($formatter->isPositiveValueBelowDisplayMinimum(0.009))->toBeTrue();
});

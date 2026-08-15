<?php

use App\Services\FoodWeightInputService;

it('trims a weight input', function () {
    expect((new FoodWeightInputService())->normalize(' 50 '))->toBe('50');
});

it('preserves a point decimal separator', function () {
    expect((new FoodWeightInputService())->normalize('50.5'))->toBe('50.5');
});

it('converts a comma decimal separator to a point', function () {
    expect((new FoodWeightInputService())->normalize('50,5'))->toBe('50.5');
});

it('preserves an invalid input after normalization', function () {
    expect((new FoodWeightInputService())->normalize(' invalid '))->toBe('invalid');
});

it('normalizes whitespace-only input to an empty string', function () {
    expect((new FoodWeightInputService())->normalize('   '))->toBe('');
});

it('identifies a non-numeric weight input', function () {
    $service = new FoodWeightInputService();

    expect($service->isNumeric('invalid'))->toBeFalse();
    expect($service->isValid('invalid'))->toBeFalse();
});

it('rejects an empty weight input', function () {
    expect((new FoodWeightInputService())->isValid(''))->toBeFalse();
});

it('rejects zero as a positive weight', function () {
    $service = new FoodWeightInputService();

    expect($service->isPositive('0'))->toBeFalse();
    expect($service->isValid('0'))->toBeFalse();
});

it('rejects a negative weight as positive', function () {
    $service = new FoodWeightInputService();

    expect($service->isPositive('-1'))->toBeFalse();
    expect($service->isValid('-1'))->toBeFalse();
});

it('accepts a positive numeric weight', function () {
    expect((new FoodWeightInputService())->isValid('0.01'))->toBeTrue();
});

it('accepts a comma decimal weight', function () {
    expect((new FoodWeightInputService())->isValid('50,5'))->toBeTrue();
});

it('accepts the maximum weight', function () {
    expect((new FoodWeightInputService())->isValid('10000'))->toBeTrue();
});

it('rejects a weight above the maximum', function () {
    expect((new FoodWeightInputService())->isValid('10000.01'))->toBeFalse();
});

it('detects only weights above the maximum', function () {
    $service = new FoodWeightInputService();

    expect($service->exceedsMaximum('10000'))->toBeFalse();
    expect($service->exceedsMaximum('10000.01'))->toBeTrue();
});

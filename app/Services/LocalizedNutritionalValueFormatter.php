<?php

namespace App\Services;

class LocalizedNutritionalValueFormatter
{
    public const MINIMUM_DISPLAYABLE_POSITIVE_VALUE = 0.01;

    public function format(int|float $value): string
    {
        $isEnglishLocale = app()->getLocale() === 'en';
        $decimalSeparator = $isEnglishLocale ? '.' : ',';
        $thousandsSeparator = $isEnglishLocale ? ',' : '.';

        return rtrim(rtrim(number_format((float) $value, 2, $decimalSeparator, $thousandsSeparator), '0'), $decimalSeparator);
    }

    public function formatDisplayValue(int|float $value): string
    {
        if ($this->isPositiveValueBelowDisplayMinimum($value)) {
            return '< '.$this->format(self::MINIMUM_DISPLAYABLE_POSITIVE_VALUE);
        }

        return $this->format($value);
    }

    public function isPositiveValueBelowDisplayMinimum(int|float $value): bool
    {
        $value = (float) $value;

        return $value > 0 && $value < self::MINIMUM_DISPLAYABLE_POSITIVE_VALUE;
    }
}

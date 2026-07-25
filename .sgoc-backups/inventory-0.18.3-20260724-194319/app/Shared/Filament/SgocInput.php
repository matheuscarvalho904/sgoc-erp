<?php

declare(strict_types=1);

namespace App\Shared\Filament;

use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;

final class SgocInput
{
    public static function money(string $name): TextInput
    {
        return TextInput::make($name)
            ->prefix('R$')
            ->inputMode('decimal')
            ->mask(RawJs::make(<<<'JS'
                $money($input, ',', '.', 2)
            JS))
            ->stripCharacters(['.', ','])
            ->dehydrateStateUsing(fn ($state): ?string => self::normalizeDecimal($state, 2))
            ->formatStateUsing(fn ($state): ?string => self::formatDecimal($state, 2))
            ->extraInputAttributes(['class' => 'text-right tabular-nums']);
    }

    public static function quantity(string $name, int $scale = 4): TextInput
    {
        return TextInput::make($name)
            ->inputMode('decimal')
            ->mask(RawJs::make("\$money(\$input, ',', '.', {$scale})"))
            ->stripCharacters(['.', ','])
            ->dehydrateStateUsing(fn ($state): ?string => self::normalizeDecimal($state, $scale))
            ->formatStateUsing(fn ($state): ?string => self::formatDecimal($state, $scale))
            ->extraInputAttributes(['class' => 'text-right tabular-nums']);
    }

    public static function percentage(string $name, int $scale = 2): TextInput
    {
        return self::quantity($name, $scale)
            ->suffix('%')
            ->minValue(0)
            ->maxValue(100);
    }

    private static function normalizeDecimal(mixed $value, int $scale): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);
        $value = preg_replace('/[^0-9,.-]/', '', $value) ?? '';

        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        if (! is_numeric($value)) {
            return null;
        }

        return number_format((float) $value, $scale, '.', '');
    }

    private static function formatDecimal(mixed $value, int $scale): ?string
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return $value === null ? null : (string) $value;
        }

        return number_format((float) $value, $scale, ',', '.');
    }
}

<?php

declare(strict_types=1);

namespace App\Support\Filament;

use Filament\Forms\Components\TextInput;

final class BrazilianInput
{
    public static function cnpj(string $name = 'document'): TextInput
    {
        return TextInput::make($name)
            ->placeholder('00.000.000/0000-00')
            ->maxLength(18)
            ->extraInputAttributes([
                'inputmode' => 'numeric',
                'oninput' => "let v=this.value.replace(/\\D/g,'').slice(0,14); v=v.replace(/^(\\d{2})(\\d)/,'$1.$2').replace(/^(\\d{2})\\.(\\d{3})(\\d)/,'$1.$2.$3').replace(/\\.(\\d{3})(\\d)/,'.$1/$2').replace(/(\\d{4})(\\d)/,'$1-$2'); this.value=v;",
            ]);
    }

    public static function cpfCnpj(string $name = 'document'): TextInput
    {
        return TextInput::make($name)
            ->placeholder('CPF ou CNPJ')
            ->maxLength(18)
            ->extraInputAttributes([
                'inputmode' => 'numeric',
                'oninput' => "let v=this.value.replace(/\\D/g,'').slice(0,14); if(v.length<=11){v=v.replace(/^(\\d{3})(\\d)/,'$1.$2').replace(/^(\\d{3})\\.(\\d{3})(\\d)/,'$1.$2.$3').replace(/\\.(\\d{3})(\\d)/,'.$1-$2')}else{v=v.replace(/^(\\d{2})(\\d)/,'$1.$2').replace(/^(\\d{2})\\.(\\d{3})(\\d)/,'$1.$2.$3').replace(/\\.(\\d{3})(\\d)/,'.$1/$2').replace(/(\\d{4})(\\d)/,'$1-$2')} this.value=v;",
            ]);
    }

    public static function phone(string $name = 'phone'): TextInput
    {
        return TextInput::make($name)
            ->tel()
            ->placeholder('(00) 00000-0000')
            ->maxLength(15)
            ->extraInputAttributes([
                'inputmode' => 'tel',
                'oninput' => "let v=this.value.replace(/\\D/g,'').slice(0,11); v=v.replace(/^(\\d{2})(\\d)/,'($1) $2').replace(/(\\d{5})(\\d)/,'$1-$2'); this.value=v;",
            ]);
    }

    public static function cep(string $name = 'zip_code'): TextInput
    {
        return TextInput::make($name)
            ->placeholder('00000-000')
            ->maxLength(9)
            ->extraInputAttributes([
                'inputmode' => 'numeric',
                'oninput' => "let v=this.value.replace(/\\D/g,'').slice(0,8); v=v.replace(/^(\\d{5})(\\d)/,'$1-$2'); this.value=v;",
            ]);
    }

    public static function plate(string $name = 'plate'): TextInput
    {
        return TextInput::make($name)
            ->placeholder('ABC1D23')
            ->maxLength(7)
            ->extraInputAttributes([
                'style' => 'text-transform: uppercase',
                'oninput' => "this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'').slice(0,7);",
            ])
            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtoupper(preg_replace('/[^A-Z0-9]/', '', $state)) : null);
    }

    public static function renavam(string $name = 'renavam'): TextInput
    {
        return TextInput::make($name)
            ->maxLength(11)
            ->extraInputAttributes([
                'inputmode' => 'numeric',
                'oninput' => "this.value=this.value.replace(/\\D/g,'').slice(0,11);",
            ]);
    }

    public static function chassis(string $name = 'chassis'): TextInput
    {
        return TextInput::make($name)
            ->maxLength(17)
            ->extraInputAttributes([
                'style' => 'text-transform: uppercase',
                'oninput' => "this.value=this.value.toUpperCase().replace(/[^A-HJ-NPR-Z0-9]/g,'').slice(0,17);",
            ]);
    }

    public static function decimal(string $name, int $scale = 2): TextInput
    {
        return TextInput::make($name)
            ->numeric()
            ->step($scale === 3 ? '0.001' : '0.01')
            ->extraInputAttributes(['inputmode' => 'decimal']);
    }

    public static function money(string $name): TextInput
    {
        return self::decimal($name)->prefix('R$');
    }
}

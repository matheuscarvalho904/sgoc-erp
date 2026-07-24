<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductClasses;

use App\Filament\Resources\ProductClasses\Pages\{CreateProductClass, EditProductClass, ListProductClasses};
use App\Modules\Catalog\Domain\Models\ProductClass;
use App\Modules\Foundation\Domain\Models\Tenant;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\{Hidden, Select, Textarea, TextInput, Toggle};
use Filament\Resources\Resource;
use Filament\Schemas\Components\{Grid, Section};
use Filament\Schemas\Schema;
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Table;
use UnitEnum;

final class ProductClassResource extends Resource
{
    protected static ?string $model = ProductClass::class;
    protected static ?string $modelLabel = 'classe de produto';
    protected static ?string $pluralModelLabel = 'classes de produtos';
    protected static ?string $navigationLabel = 'Classes de Produtos';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static string|UnitEnum|null $navigationGroup = 'Cadastros Gerais';
    protected static ?int $navigationSort = 55;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn()=>Tenant::query()->value('id'))->required(),
            Section::make('Identificação')->columns(['default'=>1,'md'=>2,'xl'=>4])->schema([
                TextInput::make('code')->label('Código')->required()->maxLength(30),
                TextInput::make('name')->label('Nome')->required()->columnSpan(2),
                Select::make('status')->label('Status')->options(['active'=>'Ativa','inactive'=>'Inativa'])->default('active')->required(),
                Textarea::make('description')->label('Descrição')->columnSpanFull(),
            ]),
            Section::make('Comportamentos')->columns(['default'=>1,'md'=>2,'xl'=>4])->schema([
                Toggle::make('controls_stock')->label('Controla estoque')->default(true),
                Toggle::make('requires_lot')->label('Exige lote'),
                Toggle::make('requires_expiration')->label('Exige validade'),
                Toggle::make('requires_asset')->label('Exige patrimônio'),
                Toggle::make('allows_purchase')->label('Permite compra')->default(true),
                Toggle::make('allows_sale')->label('Permite venda'),
                Toggle::make('allows_os_consumption')->label('Permite consumo em OS')->default(true),
                Toggle::make('allows_fueling')->label('Permite abastecimento'),
                Toggle::make('generates_depreciation')->label('Gera depreciação'),
                Toggle::make('controls_serial_number')->label('Controla número de série'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Código')->searchable()->sortable(),
            TextColumn::make('name')->label('Classe')->searchable()->sortable()->weight('bold'),
            IconColumn::make('controls_stock')->label('Estoque')->boolean(),
            IconColumn::make('allows_purchase')->label('Compra')->boolean(),
            IconColumn::make('requires_asset')->label('Patrimônio')->boolean(),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn(string $state)=>$state==='active'?'Ativa':'Inativa')->color(fn(string $state)=>$state==='active'?'success':'gray'),
        ])->recordActions([EditAction::make()->label('Editar')]);
    }

    public static function getPages(): array { return ['index'=>ListProductClasses::route('/'),'create'=>CreateProductClass::route('/criar'),'edit'=>EditProductClass::route('/{record}/editar')]; }
}

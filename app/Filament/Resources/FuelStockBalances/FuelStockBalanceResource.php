<?php

declare(strict_types=1);
namespace App\Filament\Resources\FuelStockBalances;
use App\Filament\Resources\FuelStockBalances\Pages\ListFuelStockBalances;
use App\Modules\Fuel\Domain\Models\FuelStockBalance;
use BackedEnum; use UnitEnum;
use Filament\Resources\Resource; use Filament\Schemas\Schema; use Filament\Tables\Columns\TextColumn; use Filament\Tables\Table;
final class FuelStockBalanceResource extends Resource {
 protected static ?string $model=FuelStockBalance::class; protected static ?string $modelLabel='Saldo de combustível'; protected static ?string $pluralModelLabel='Saldos de combustível'; protected static ?string $navigationLabel='Saldos de Combustível'; protected static string|UnitEnum|null $navigationGroup='Gestão de Combustíveis'; protected static string|BackedEnum|null $navigationIcon='heroicon-o-chart-bar-square'; protected static ?int $navigationSort=40; protected static bool $shouldRegisterNavigation=true;
 public static function form(Schema $schema): Schema { return $schema->components([]); }
 public static function table(Table $table): Table { return $table->columns([TextColumn::make('storage_id')->label('Ponto'),TextColumn::make('fuel_id')->label('Combustível'),TextColumn::make('quantity_liters')->label('Saldo')->numeric(decimalPlaces:2)->suffix(' L')->sortable(),TextColumn::make('average_cost')->label('Custo médio')->money('BRL'),TextColumn::make('total_value')->label('Valor em estoque')->money('BRL'),TextColumn::make('last_movement_at')->label('Último movimento')->dateTime('d/m/Y H:i')])->defaultSort('last_movement_at','desc'); }
 public static function getPages(): array { return ['index'=>ListFuelStockBalances::route('/')]; }
}

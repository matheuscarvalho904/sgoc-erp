<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockMovements;

use App\Filament\Resources\StockMovements\Pages\{CreateStockMovement, ListStockMovements};
use App\Modules\Inventory\Application\Services\InventoryTransactionService;
use App\Modules\Inventory\Domain\Models\StockMovement;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\{DateTimePicker, Select, Textarea, TextInput};
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\{Grid, Section};
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

final class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;
    protected static ?string $modelLabel = 'Movimentação';
    protected static ?string $pluralModelLabel = 'Movimentações';
    protected static ?string $navigationLabel = 'Movimentações';
    protected static string|UnitEnum|null $navigationGroup = 'Estoque e Almoxarifado';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Movimentação de estoque')->description('Após salvar, a movimentação será processada e não poderá ser editada. Correções devem ser feitas por estorno.')->schema([
                Grid::make(['default' => 1, 'md' => 2, 'xl' => 4])->schema([
                    TextInput::make('number')->label('Número')->default(fn (): string => 'MOV-' . now()->format('YmdHis'))->required()->maxLength(50),
                    Select::make('movement_type')->label('Tipo')->options([
                        'in' => 'Entrada', 'out' => 'Saída',
                        'adjustment_in' => 'Ajuste positivo', 'adjustment_out' => 'Ajuste negativo',
                        'return_in' => 'Devolução', 'consumption_out' => 'Consumo',
                    ])->required()->native(false),
                    Select::make('warehouse_id')->label('Almoxarifado')->relationship('warehouse', 'name')->searchable()->preload()->required(),
                    Select::make('location_id')->label('Localização')->relationship('location', 'name')->searchable()->preload(),
                    Select::make('product_id')->label('Produto')->relationship('product', 'name')->searchable()->preload()->required(),
                    TextInput::make('quantity')->label('Quantidade')->numeric()->minValue(0.0001)->required(),
                    TextInput::make('unit_cost')->label('Custo unitário')->numeric()->minValue(0)->default(0)->prefix('R$'),
                    DateTimePicker::make('occurred_at')->label('Data e hora')->default(now())->required(),
                    TextInput::make('lot_number')->label('Lote')->maxLength(100),
                    TextInput::make('reason')->label('Motivo')->maxLength(60),
                    Textarea::make('notes')->label('Justificativa/observações')->columnSpanFull(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('occurred_at', 'desc')->columns([
            TextColumn::make('number')->label('Número')->searchable(),
            TextColumn::make('status')->label('Situação')->badge()->formatStateUsing(fn (?string $state): string => match ($state) { 'posted' => 'Processada', 'reversed' => 'Estornada', default => 'Rascunho' })->color(fn (?string $state): string => match ($state) { 'posted' => 'success', 'reversed' => 'danger', default => 'gray' }),
            TextColumn::make('movement_type')->label('Tipo')->badge()->formatStateUsing(fn (?string $state): string => in_array($state, ['out','adjustment_out','consumption_out','transfer_out'], true) ? 'Saída' : 'Entrada')->color(fn (?string $state): string => in_array($state, ['out','adjustment_out','consumption_out','transfer_out'], true) ? 'danger' : 'success'),
            TextColumn::make('warehouse.name')->label('Almoxarifado'),
            TextColumn::make('product.name')->label('Produto')->searchable(),
            TextColumn::make('quantity')->label('Quantidade')->numeric(decimalPlaces: 4),
            TextColumn::make('balance_before')->label('Saldo anterior')->numeric(decimalPlaces: 4)->toggleable(),
            TextColumn::make('balance_after')->label('Saldo posterior')->numeric(decimalPlaces: 4)->toggleable(),
            TextColumn::make('total_cost')->label('Valor total')->money('BRL', locale: 'pt_BR'),
            TextColumn::make('occurred_at')->label('Data')->dateTime('d/m/Y H:i'),
        ])->recordActions([
            Action::make('reverse')->label('Estornar')->icon('heroicon-o-arrow-uturn-left')->color('danger')->requiresConfirmation()->modalDescription('Será criada uma movimentação inversa. O lançamento original permanecerá no histórico.')->visible(fn (StockMovement $record): bool => $record->status === 'posted' && $record->reversed_at === null)->action(function (StockMovement $record): void {
                try {
                    app(InventoryTransactionService::class)->reverse($record);
                    Notification::make()->title('Movimentação estornada')->success()->send();
                } catch (\Throwable $exception) {
                    Notification::make()->title('Não foi possível estornar')->body($exception->getMessage())->danger()->send();
                }
            }),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListStockMovements::route('/'), 'create' => CreateStockMovement::route('/criar')];
    }
}

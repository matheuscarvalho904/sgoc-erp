<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuotationItems;

use App\Filament\Resources\QuotationItems\Pages\CreateQuotationItem;
use App\Filament\Resources\QuotationItems\Pages\EditQuotationItem;
use App\Filament\Resources\QuotationItems\Pages\ListQuotationItems;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Purchasing\Domain\Models\PurchaseRequestItem;
use App\Modules\Purchasing\Domain\Models\QuotationItem;
use App\Modules\Purchasing\Domain\Models\QuotationSupplier;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class QuotationItemResource extends Resource
{
    protected static ?string $model = QuotationItem::class;
    protected static ?string $recordTitleAttribute = 'brand';
    protected static ?string $modelLabel = 'item da proposta';
    protected static ?string $pluralModelLabel = 'itens das propostas';
    protected static ?string $navigationLabel = 'Itens das propostas';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';
    protected static string|UnitEnum|null $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 65;
    protected static ?string $slug = 'compras/itens-propostas';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')
                ->default(fn (): mixed => Tenant::query()->value('id'))
                ->required(),

            Section::make('Preço cotado por item')->schema([
                Grid::make(['default' => 1, 'md' => 2, 'xl' => 4])->schema([
                    Select::make('quotation_supplier_id')
                        ->label('Proposta / fornecedor')
                        ->options(fn (): array => QuotationSupplier::query()
                            ->with(['quotationRequest', 'supplier'])
                            ->latest()
                            ->get()
                            ->mapWithKeys(fn (QuotationSupplier $record): array => [
                                $record->id => sprintf(
                                    '%s — %s',
                                    $record->quotationRequest?->number ?? 'Cotação',
                                    $record->supplier?->trade_name ?? 'Fornecedor',
                                ),
                            ])->all())
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required()
                        ->columnSpan(2),

                    Select::make('purchase_request_item_id')
                        ->label('Item solicitado')
                        ->options(function (callable $get): array {
                            $proposalId = $get('quotation_supplier_id');

                            if (blank($proposalId)) {
                                return [];
                            }

                            $proposal = QuotationSupplier::query()->find($proposalId);

                            if ($proposal === null) {
                                return [];
                            }

                            return PurchaseRequestItem::query()
                                ->where('purchase_request_id', $proposal->quotationRequest?->purchase_request_id)
                                ->orderBy('description')
                                ->get()
                                ->mapWithKeys(fn (PurchaseRequestItem $item): array => [
                                    $item->id => sprintf('%s — %s %s', $item->description, $item->quantity, $item->unit),
                                ])->all();
                        })
                        ->searchable()
                        ->required()
                        ->columnSpan(2),

                    TextInput::make('quantity')->label('Quantidade cotada')->numeric()->minValue(0.0001)->required(),
                    TextInput::make('unit_price')->label('Preço unitário')->numeric()->prefix('R$')->minValue(0)->required(),
                    TextInput::make('discount_amount')->label('Desconto no item')->numeric()->prefix('R$')->minValue(0)->default(0),
                    TextInput::make('tax_amount')->label('Impostos no item')->numeric()->prefix('R$')->minValue(0)->default(0),
                    TextInput::make('brand')->label('Marca oferecida')->maxLength(120)->columnSpan(2),
                    Toggle::make('selected')->label('Item selecionado')->helperText('Use na futura compra fracionada por fornecedor.'),
                    Textarea::make('notes')->label('Observações técnicas')->rows(3)->columnSpanFull(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quotationSupplier.quotationRequest.number')->label('Cotação')->searchable()->sortable(),
                TextColumn::make('quotationSupplier.supplier.trade_name')->label('Fornecedor')->searchable()->weight('bold'),
                TextColumn::make('purchaseRequestItem.description')->label('Item')->searchable()->limit(45),
                TextColumn::make('brand')->label('Marca')->placeholder('—')->searchable(),
                TextColumn::make('quantity')->label('Qtd.')->numeric(decimalPlaces: 4)->sortable(),
                TextColumn::make('unit_price')->label('Preço unitário')->money('BRL')->sortable(),
                TextColumn::make('discount_amount')->label('Desconto')->money('BRL')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tax_amount')->label('Impostos')->money('BRL')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_amount')->label('Total do item')->money('BRL')->sortable()->weight('bold'),
                IconColumn::make('selected')->label('Selecionado')->boolean(),
            ])
            ->filters([
                SelectFilter::make('quotation_supplier_id')
                    ->label('Proposta')
                    ->relationship('quotationSupplier', 'proposal_number'),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Excluir selecionados'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'quotationSupplier.quotationRequest',
            'quotationSupplier.supplier',
            'purchaseRequestItem',
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuotationItems::route('/'),
            'create' => CreateQuotationItem::route('/criar'),
            'edit' => EditQuotationItem::route('/{record}/editar'),
        ];
    }
}

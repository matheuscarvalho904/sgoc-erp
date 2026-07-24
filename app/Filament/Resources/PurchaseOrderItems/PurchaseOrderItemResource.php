<?php

declare(strict_types=1);
namespace App\Filament\Resources\PurchaseOrderItems;
use App\Filament\Resources\PurchaseOrderItems\Pages\CreatePurchaseOrderItem;
use App\Filament\Resources\PurchaseOrderItems\Pages\EditPurchaseOrderItem;
use App\Filament\Resources\PurchaseOrderItems\Pages\ListPurchaseOrderItems;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Purchasing\Domain\Models\PurchaseOrder;
use App\Modules\Purchasing\Domain\Models\PurchaseOrderItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;
final class PurchaseOrderItemResource extends Resource
{
    protected static ?string $model = PurchaseOrderItem::class;
    protected static ?string $modelLabel = 'item do pedido';
    protected static ?string $pluralModelLabel = 'itens dos pedidos';
    protected static ?string $navigationLabel = 'Itens dos pedidos';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';
    protected static string|UnitEnum|null $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 80;
    protected static ?string $slug = 'compras/itens-pedidos';
    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn()=>Tenant::query()->value('id'))->required(),
            Section::make('Item do pedido')->schema([Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([
                Select::make('purchase_order_id')->label('Pedido')->options(fn()=>PurchaseOrder::query()->orderByDesc('ordered_at')->pluck('number','id')->all())->searchable()->preload()->required()->columnSpan(2),
                TextInput::make('sequence')->label('Sequência')->numeric()->minValue(1)->required(),
                Select::make('status')->label('Status')->default('pending')->required()->options(['pending'=>'Pendente','partially_received'=>'Recebido parcialmente','received'=>'Recebido','cancelled'=>'Cancelado']),
                TextInput::make('description')->label('Descrição')->required()->maxLength(250)->columnSpan(2),
                TextInput::make('unit')->label('Unidade')->default('UN')->required(),
                TextInput::make('quantity')->label('Quantidade')->numeric()->minValue(0.0001)->required(),
                TextInput::make('unit_price')->label('Preço unitário')->numeric()->prefix('R$')->minValue(0)->required(),
                TextInput::make('discount_amount')->label('Desconto')->numeric()->prefix('R$')->minValue(0)->default(0),
                TextInput::make('tax_amount')->label('Impostos')->numeric()->prefix('R$')->minValue(0)->default(0),
                TextInput::make('quantity_received')->label('Quantidade recebida')->numeric()->minValue(0)->default(0),
                Textarea::make('specification')->label('Especificação')->rows(3)->columnSpanFull(),
                Textarea::make('notes')->label('Observações')->rows(3)->columnSpanFull(),
            ])]),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('purchaseOrder.number')->label('Pedido')->searchable()->sortable(),
            TextColumn::make('sequence')->label('#')->sortable(),
            TextColumn::make('description')->label('Item')->searchable()->limit(60)->weight('bold'),
            TextColumn::make('quantity')->label('Quantidade')->numeric(decimalPlaces: 4)->suffix(fn(PurchaseOrderItem $r)=>' '.$r->unit),
            TextColumn::make('unit_price')->label('Preço unitário')->money('BRL'),
            TextColumn::make('total_amount')->label('Total')->money('BRL')->sortable(),
            TextColumn::make('quantity_received')->label('Recebido')->numeric(decimalPlaces: 4),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn(string $s)=>match($s){'pending'=>'Pendente','partially_received'=>'Recebido parcialmente','received'=>'Recebido','cancelled'=>'Cancelado',default=>$s}),
        ])->filters([SelectFilter::make('status')->label('Status')->options(['pending'=>'Pendente','partially_received'=>'Recebido parcialmente','received'=>'Recebido','cancelled'=>'Cancelado'])])
          ->recordActions([EditAction::make()->label('Editar')])
          ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionados')])]);
    }
    public static function getPages(): array { return ['index'=>ListPurchaseOrderItems::route('/'),'create'=>CreatePurchaseOrderItem::route('/criar'),'edit'=>EditPurchaseOrderItem::route('/{record}/editar')]; }
}

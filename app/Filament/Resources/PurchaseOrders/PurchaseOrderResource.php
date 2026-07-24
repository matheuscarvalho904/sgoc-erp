<?php

declare(strict_types=1);

namespace App\Filament\Resources\PurchaseOrders;

use App\Filament\Resources\PurchaseOrders\Pages\CreatePurchaseOrder;
use App\Filament\Resources\PurchaseOrders\Pages\EditPurchaseOrder;
use App\Filament\Resources\PurchaseOrders\Pages\ListPurchaseOrders;
use App\Modules\Foundation\Domain\Models\Branch;
use App\Modules\Foundation\Domain\Models\Company;
use App\Modules\Foundation\Domain\Models\CostCenter;
use App\Modules\Foundation\Domain\Models\Organization;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Foundation\Domain\Models\Work;
use App\Modules\Purchasing\Domain\Models\PurchaseOrder;
use App\Modules\Purchasing\Domain\Models\Supplier;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
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

final class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $recordTitleAttribute = 'number';
    protected static ?string $modelLabel = 'pedido de compra';
    protected static ?string $pluralModelLabel = 'pedidos de compra';
    protected static ?string $navigationLabel = 'Pedidos de compra';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';
    protected static string|UnitEnum|null $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 70;
    protected static ?string $slug = 'compras/pedidos';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn () => Tenant::query()->value('id'))->required(),
            Section::make('Identificação')->schema([
                Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([
                    TextInput::make('number')->label('Número')->required()->maxLength(40),
                    DatePicker::make('ordered_at')->label('Data do pedido')->default(now())->required(),
                    DatePicker::make('expected_at')->label('Previsão de entrega'),
                    Select::make('status')->label('Status')->required()->default('draft')->options([
                        'draft'=>'Rascunho','approved'=>'Aprovado','issued'=>'Emitido','partially_received'=>'Recebido parcialmente','received'=>'Recebido','cancelled'=>'Cancelado',
                    ]),
                    Select::make('organization_id')->label('Organização')->options(fn()=>Organization::query()->pluck('name','id')->all())->searchable()->preload()->required(),
                    Select::make('company_id')->label('Empresa')->options(fn()=>Company::query()->pluck('trade_name','id')->all())->searchable()->preload()->required(),
                    Select::make('branch_id')->label('Filial')->options(fn()=>Branch::query()->pluck('name','id')->all())->searchable()->preload()->required(),
                    Select::make('supplier_id')->label('Fornecedor')->options(fn()=>Supplier::query()->orderBy('trade_name')->pluck('trade_name','id')->all())->searchable()->preload()->required(),
                    Select::make('work_id')->label('Obra')->options(fn()=>Work::query()->pluck('name','id')->all())->searchable()->preload(),
                    Select::make('cost_center_id')->label('Centro de custo')->options(fn()=>CostCenter::query()->pluck('name','id')->all())->searchable()->preload(),
                    TextInput::make('payment_terms')->label('Condição de pagamento')->maxLength(180)->columnSpan(2),
                    TextInput::make('delivery_location')->label('Local de entrega')->maxLength(250)->columnSpan(2),
                ]),
            ]),
            Section::make('Valores')->schema([
                Grid::make(['default'=>1,'md'=>2,'xl'=>5])->schema([
                    TextInput::make('subtotal')->label('Subtotal')->numeric()->prefix('R$')->default(0)->required(),
                    TextInput::make('freight_amount')->label('Frete')->numeric()->prefix('R$')->default(0)->required(),
                    TextInput::make('discount_amount')->label('Desconto')->numeric()->prefix('R$')->default(0)->required(),
                    TextInput::make('other_amount')->label('Outros')->numeric()->prefix('R$')->default(0)->required(),
                    TextInput::make('total_amount')->label('Total')->numeric()->prefix('R$')->default(0)->required(),
                    Textarea::make('notes')->label('Observações')->rows(4)->columnSpanFull(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('number')->label('Pedido')->searchable()->sortable()->weight('bold'),
            TextColumn::make('supplier.trade_name')->label('Fornecedor')->searchable()->sortable(),
            TextColumn::make('purchaseRequest.number')->label('Solicitação')->searchable(),
            TextColumn::make('work.name')->label('Obra')->placeholder('—')->toggleable(),
            TextColumn::make('ordered_at')->label('Emissão')->date('d/m/Y')->sortable(),
            TextColumn::make('expected_at')->label('Previsão')->date('d/m/Y')->placeholder('—')->sortable(),
            TextColumn::make('total_amount')->label('Total')->money('BRL')->sortable(),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn(string $s)=>match($s){'draft'=>'Rascunho','approved'=>'Aprovado','issued'=>'Emitido','partially_received'=>'Recebido parcialmente','received'=>'Recebido','cancelled'=>'Cancelado',default=>$s})->color(fn(string $s)=>match($s){'approved','received'=>'success','issued'=>'info','partially_received'=>'warning','cancelled'=>'danger',default=>'gray'}),
        ])->filters([
            SelectFilter::make('status')->label('Status')->options(['draft'=>'Rascunho','approved'=>'Aprovado','issued'=>'Emitido','partially_received'=>'Recebido parcialmente','received'=>'Recebido','cancelled'=>'Cancelado']),
        ])->recordActions([EditAction::make()->label('Editar')])
          ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionados')])])
          ->defaultSort('created_at','desc');
    }

    public static function getPages(): array
    {
        return ['index'=>ListPurchaseOrders::route('/'),'create'=>CreatePurchaseOrder::route('/criar'),'edit'=>EditPurchaseOrder::route('/{record}/editar')];
    }
}

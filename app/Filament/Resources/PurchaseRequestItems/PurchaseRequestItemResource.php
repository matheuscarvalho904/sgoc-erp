<?php

declare(strict_types=1);

namespace App\Filament\Resources\PurchaseRequestItems;

use App\Filament\Resources\PurchaseRequestItems\Pages\{CreatePurchaseRequestItem, EditPurchaseRequestItem, ListPurchaseRequestItems};
use App\Modules\Catalog\Domain\Models\{Product, Unit};
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Purchasing\Domain\Models\{ApplicationType, PurchaseRequest, PurchaseRequestItem};
use App\Shared\Filament\SgocInput;
use BackedEnum;
use Filament\Actions\{BulkActionGroup, DeleteBulkAction, EditAction};
use Filament\Forms\Components\{Hidden, Select, Textarea, TextInput};
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class PurchaseRequestItemResource extends Resource
{
    protected static ?string $model = PurchaseRequestItem::class;
    protected static ?string $recordTitleAttribute = 'description';
    protected static ?string $modelLabel = 'item da solicitação';
    protected static ?string $pluralModelLabel = 'itens das solicitações';
    protected static ?string $navigationLabel = 'Itens das solicitações';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';
    protected static string|UnitEnum|null $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 31;
    protected static ?string $slug = 'compras/itens-solicitacoes';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn () => Tenant::query()->value('id'))->required(),
            Section::make('Identificação do item')->columnSpanFull()->columns(['default' => 1, 'md' => 2, 'xl' => 4])->schema([
                Select::make('purchase_request_id')->label('Solicitação')->options(fn () => PurchaseRequest::query()->orderByDesc('requested_at')->pluck('number', 'id'))->searchable()->preload()->required()->columnSpan(['default' => 1, 'xl' => 2]),
                TextInput::make('sequence')->label('Sequência')->numeric()->required()->minValue(1),
                Select::make('status')->label('Status')->default('pending')->required()->options(['pending' => 'Pendente', 'approved' => 'Aprovado', 'rejected' => 'Rejeitado', 'cancelled' => 'Cancelado']),
                Select::make('item_type')->label('Tipo do item')->options(['product' => 'Produto/Material', 'service' => 'Serviço'])->default('product')->required()->live(),
                Select::make('product_id')->label('Produto/Material')->options(fn () => Product::query()->where('status', 'active')->where('product_type', '!=', 'service')->orderBy('name')->get()->mapWithKeys(fn (Product $product) => [$product->id => $product->code . ' — ' . $product->name]))->searchable()->preload()->visible(fn (Get $get) => $get('item_type') === 'product')->required(fn (Get $get) => $get('item_type') === 'product')->live()->afterStateUpdated(function ($state, Set $set): void {
                    if (! $state || ! ($product = Product::query()->find($state))) return;
                    $set('description', $product->name);
                    $set('unit_id', $product->unit_id);
                    $unit = Unit::query()->find($product->unit_id);
                    $set('unit', $unit?->code ?? 'UN');
                })->columnSpan(['default' => 1, 'xl' => 2]),
                TextInput::make('service_description')->label('Descrição do serviço')->maxLength(500)->visible(fn (Get $get) => $get('item_type') === 'service')->required(fn (Get $get) => $get('item_type') === 'service')->live()->afterStateUpdated(fn ($state, Set $set) => $set('description', $state))->columnSpan(['default' => 1, 'xl' => 3]),
                Hidden::make('description'),
                Select::make('unit_id')->label('Unidade')->options(fn () => Unit::query()->where('status', 'active')->orderBy('name')->get()->mapWithKeys(fn (Unit $unit) => [$unit->id => $unit->code . ' — ' . $unit->name]))->searchable()->preload()->required()->live()->afterStateUpdated(function ($state, Set $set): void { $set('unit', Unit::query()->find($state)?->code ?? 'UN'); }),
                Hidden::make('unit')->default('UN'),
                SgocInput::quantity('quantity')->label('Quantidade')->required()->minValue(0.0001)->default('1,0000'),
                SgocInput::money('estimated_unit_price')->label('Valor unitário estimado')->required()->default('0,00'),
                Textarea::make('specification')->label('Especificação técnica')->rows(4)->columnSpanFull(),
            ]),
            Section::make('Aplicação e apropriação')->description('Informe onde o custo será aplicado. Os destinos completos serão vinculados conforme os módulos de Ativos, Contratos, Estoque e Produção forem ativados.')->columnSpanFull()->columns(['default' => 1, 'md' => 2, 'xl' => 4])->schema([
                Select::make('application_type_id')->label('Tipo de aplicação')->options(fn () => ApplicationType::query()->where('status', 'active')->orderBy('sort_order')->pluck('name', 'id'))->searchable()->preload()->required()->columnSpan(['default' => 1, 'xl' => 2]),
                TextInput::make('application_label')->label('Aplicação / destino')->placeholder('Ex.: EH-01 — Escavadeira Komatsu; Obra Jardim Paraná; Contrato 015/2026')->required()->columnSpan(['default' => 1, 'xl' => 2]),
                SgocInput::percentage('allocation_percentage')->label('Percentual de rateio')->default('100,00')->required(),
                TextInput::make('application_data.compartment')->label('Compartimento')->placeholder('Ex.: motor, tanque, eixo dianteiro'),
                TextInput::make('application_data.service_order')->label('OS vinculada')->placeholder('Ex.: OS-0005980'),
                TextInput::make('application_data.budget_service')->label('Serviço / composição')->placeholder('Código ou descrição da composição'),
                TextInput::make('application_data.cash_flow_account')->label('Conta de fluxo de caixa'),
                TextInput::make('application_data.economic_result')->label('Resultado econômico'),
                Textarea::make('notes')->label('Observações')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('purchaseRequest.number')->label('Solicitação')->searchable()->sortable(),
            TextColumn::make('sequence')->label('Seq.')->sortable(),
            TextColumn::make('item_type')->label('Tipo')->badge()->formatStateUsing(fn (string $state) => $state === 'service' ? 'Serviço' : 'Produto/Material'),
            TextColumn::make('description')->label('Item')->searchable()->limit(55)->weight('bold'),
            TextColumn::make('unit')->label('Un.'),
            TextColumn::make('quantity')->label('Quantidade')->formatStateUsing(fn ($state) => number_format((float) $state, 4, ',', '.'))->sortable(),
            TextColumn::make('estimated_unit_price')->label('Valor unitário')->money('BRL', locale: 'pt_BR')->sortable(),
            TextColumn::make('estimated_total')->label('Total')->money('BRL', locale: 'pt_BR')->sortable(),
            TextColumn::make('applicationType.code')->label('Aplicação')->badge(),
            TextColumn::make('application_label')->label('Destino')->limit(35)->toggleable(),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (string $state) => match ($state) { 'pending' => 'Pendente', 'approved' => 'Aprovado', 'rejected' => 'Rejeitado', 'cancelled' => 'Cancelado', default => $state }),
        ])->filters([SelectFilter::make('item_type')->label('Tipo')->options(['product' => 'Produto/Material', 'service' => 'Serviço'])])
            ->recordActions([EditAction::make()->label('Editar')])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionados')])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPurchaseRequestItems::route('/'), 'create' => CreatePurchaseRequestItem::route('/criar'), 'edit' => EditPurchaseRequestItem::route('/{record}/editar')];
    }
}

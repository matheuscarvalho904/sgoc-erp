<?php

declare(strict_types=1);

namespace App\Filament\Resources\PurchaseRequestItems;

use App\Filament\Resources\PurchaseRequestItems\Pages\CreatePurchaseRequestItem;
use App\Filament\Resources\PurchaseRequestItems\Pages\EditPurchaseRequestItem;
use App\Filament\Resources\PurchaseRequestItems\Pages\ListPurchaseRequestItems;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Purchasing\Domain\Models\PurchaseRequest;
use App\Modules\Purchasing\Domain\Models\PurchaseRequestItem;
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
        return $schema->components([
            Hidden::make('tenant_id')->default(fn () => Tenant::query()->value('id'))->required(),
            Section::make('Item')->schema([
                Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([
                    Select::make('purchase_request_id')->label('Solicitação')->options(fn () => PurchaseRequest::query()->orderByDesc('requested_at')->pluck('number','id')->all())->searchable()->preload()->required()->columnSpan(2),
                    TextInput::make('sequence')->label('Sequência')->numeric()->required()->minValue(1),
                    Select::make('status')->label('Status')->default('pending')->required()->options(['pending'=>'Pendente','approved'=>'Aprovado','rejected'=>'Rejeitado','cancelled'=>'Cancelado']),
                    TextInput::make('description')->label('Descrição')->required()->maxLength(250)->columnSpan(2),
                    TextInput::make('unit')->label('Unidade')->default('UN')->required()->maxLength(20),
                    TextInput::make('quantity')->label('Quantidade')->numeric()->required()->minValue(0.0001),
                    TextInput::make('estimated_unit_price')->label('Preço unitário estimado')->numeric()->prefix('R$')->default(0)->required(),
                    Textarea::make('specification')->label('Especificação técnica')->rows(4)->columnSpanFull(),
                    Textarea::make('notes')->label('Observações')->rows(3)->columnSpanFull(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('purchaseRequest.number')->label('Solicitação')->searchable()->sortable(),
            TextColumn::make('sequence')->label('Seq.')->sortable(),
            TextColumn::make('description')->label('Item')->searchable()->limit(60)->weight('bold'),
            TextColumn::make('unit')->label('Un.'),
            TextColumn::make('quantity')->label('Quantidade')->numeric(decimalPlaces: 4)->sortable(),
            TextColumn::make('estimated_unit_price')->label('Preço unitário')->money('BRL')->sortable(),
            TextColumn::make('estimated_total')->label('Total')->money('BRL')->sortable(),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn(string $s)=>match($s){'pending'=>'Pendente','approved'=>'Aprovado','rejected'=>'Rejeitado','cancelled'=>'Cancelado',default=>$s})->color(fn(string $s)=>match($s){'approved'=>'success','rejected','cancelled'=>'danger',default=>'warning'}),
        ])->filters([SelectFilter::make('status')->options(['pending'=>'Pendente','approved'=>'Aprovado','rejected'=>'Rejeitado','cancelled'=>'Cancelado'])])
          ->recordActions([EditAction::make()->label('Editar')])
          ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionados')])]);
    }

    public static function getPages(): array
    {
        return ['index'=>ListPurchaseRequestItems::route('/'),'create'=>CreatePurchaseRequestItem::route('/criar'),'edit'=>EditPurchaseRequestItem::route('/{record}/editar')];
    }
}

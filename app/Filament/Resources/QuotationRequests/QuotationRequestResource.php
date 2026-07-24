<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuotationRequests;

use App\Filament\Resources\QuotationRequests\Pages\CreateQuotationRequest;
use App\Filament\Resources\QuotationRequests\Pages\EditQuotationRequest;
use App\Filament\Resources\QuotationRequests\Pages\ListQuotationRequests;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Purchasing\Domain\Models\PurchaseRequest;
use App\Modules\Purchasing\Domain\Models\QuotationRequest;
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

final class QuotationRequestResource extends Resource
{
    protected static ?string $model = QuotationRequest::class;
    protected static ?string $recordTitleAttribute = 'number';
    protected static ?string $modelLabel = 'cotação';
    protected static ?string $pluralModelLabel = 'cotações';
    protected static ?string $navigationLabel = 'Cotações';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-scale';
    protected static string|UnitEnum|null $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 50;
    protected static ?string $slug = 'compras/cotacoes';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn()=>Tenant::query()->value('id'))->required(),
            Hidden::make('created_by')->default(fn()=>auth()->id()),
            Section::make('Dados da cotação')->schema([
                Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([
                    TextInput::make('number')->label('Número')->disabled()->dehydrated()->maxLength(40),
                    Select::make('purchase_request_id')->label('Solicitação de compra')->options(fn()=>PurchaseRequest::query()->whereIn('status',['approved','quoting'])->orderByDesc('requested_at')->pluck('number','id')->all())->searchable()->preload()->required()->columnSpan(2),
                    Select::make('status')->label('Status')->default('draft')->required()->options(['draft'=>'Rascunho','sent'=>'Enviada','partially_answered'=>'Parcialmente respondida','answered'=>'Respondida','under_analysis'=>'Em análise','closed'=>'Encerrada','cancelled'=>'Cancelada']),
                    DatePicker::make('issued_at')->label('Data de emissão')->default(now())->required(),
                    DatePicker::make('response_deadline')->label('Prazo para resposta')->minDate(fn($get)=>$get('issued_at')),
                    Textarea::make('notes')->label('Observações')->rows(4)->columnSpanFull(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('number')->label('Cotação')->searchable()->sortable()->weight('bold'),
            TextColumn::make('purchaseRequest.number')->label('Solicitação')->searchable(),
            TextColumn::make('purchaseRequest.company.name')->label('Empresa')->searchable(),
            TextColumn::make('purchaseRequest.work.name')->label('Obra')->placeholder('—'),
            TextColumn::make('issued_at')->label('Emissão')->date('d/m/Y')->sortable(),
            TextColumn::make('response_deadline')->label('Prazo')->date('d/m/Y')->placeholder('—')->sortable(),
            TextColumn::make('suppliers_count')->counts('suppliers')->label('Fornecedores'),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn(string $s)=>match($s){'draft'=>'Rascunho','sent'=>'Enviada','partially_answered'=>'Parcialmente respondida','answered'=>'Respondida','under_analysis'=>'Em análise','closed'=>'Encerrada','cancelled'=>'Cancelada',default=>$s})->color(fn(string $s)=>match($s){'closed'=>'success','cancelled'=>'danger','sent','under_analysis'=>'warning','answered','partially_answered'=>'info',default=>'gray'}),
        ])->filters([SelectFilter::make('status')->label('Status')->options(['draft'=>'Rascunho','sent'=>'Enviada','partially_answered'=>'Parcialmente respondida','answered'=>'Respondida','under_analysis'=>'Em análise','closed'=>'Encerrada','cancelled'=>'Cancelada'])])
          ->recordActions([EditAction::make()->label('Editar')])
          ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionadas')])])
          ->defaultSort('issued_at','desc');
    }

    public static function getPages(): array
    {
        return ['index'=>ListQuotationRequests::route('/'),'create'=>CreateQuotationRequest::route('/criar'),'edit'=>EditQuotationRequest::route('/{record}/editar')];
    }
}

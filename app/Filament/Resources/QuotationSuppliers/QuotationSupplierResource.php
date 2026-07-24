<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuotationSuppliers;

use App\Filament\Resources\QuotationSuppliers\Pages\CreateQuotationSupplier;
use App\Filament\Resources\QuotationSuppliers\Pages\EditQuotationSupplier;
use App\Filament\Resources\QuotationSuppliers\Pages\ListQuotationSuppliers;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Purchasing\Domain\Models\QuotationRequest;
use App\Modules\Purchasing\Domain\Models\QuotationSupplier;
use App\Modules\Purchasing\Domain\Models\Supplier;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
use UnitEnum;

final class QuotationSupplierResource extends Resource
{
    protected static ?string $model = QuotationSupplier::class;
    protected static ?string $recordTitleAttribute = 'proposal_number';
    protected static ?string $modelLabel = 'proposta de fornecedor';
    protected static ?string $pluralModelLabel = 'propostas de fornecedores';
    protected static ?string $navigationLabel = 'Propostas recebidas';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static string|UnitEnum|null $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 60;
    protected static ?string $slug = 'compras/propostas';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn()=>Tenant::query()->value('id'))->required(),
            Section::make('Fornecedor e proposta')->schema([
                Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([
                    Select::make('quotation_request_id')->label('Cotação')->options(fn()=>QuotationRequest::query()->orderByDesc('issued_at')->pluck('number','id')->all())->searchable()->preload()->required()->columnSpan(2),
                    Select::make('supplier_id')->label('Fornecedor')->options(fn()=>Supplier::query()->where('status','active')->orderBy('trade_name')->pluck('trade_name','id')->all())->searchable()->preload()->required()->columnSpan(2),
                    Select::make('status')->label('Status')->default('invited')->required()->options(['invited'=>'Convidado','viewed'=>'Visualizada','answered'=>'Respondida','declined'=>'Recusada','disqualified'=>'Desclassificada','winner'=>'Vencedora']),
                    TextInput::make('proposal_number')->label('Número da proposta')->maxLength(80),
                    DatePicker::make('proposal_date')->label('Data da proposta'),
                    DatePicker::make('validity_date')->label('Validade'),
                    TextInput::make('delivery_days')->label('Prazo de entrega (dias)')->numeric()->minValue(0),
                    TextInput::make('subtotal')->label('Subtotal')->numeric()->prefix('R$')->default(0),
                    TextInput::make('freight_amount')->label('Frete')->numeric()->prefix('R$')->default(0),
                    TextInput::make('discount_amount')->label('Desconto')->numeric()->prefix('R$')->default(0),
                    TextInput::make('other_amount')->label('Outros valores')->numeric()->prefix('R$')->default(0),
                    TextInput::make('total_amount')->label('Total da proposta')->numeric()->prefix('R$')->default(0)->required(),
                    TextInput::make('payment_terms')->label('Condição de pagamento')->maxLength(180)->columnSpan(2),
                    Toggle::make('is_winner')->label('Proposta vencedora'),
                    FileUpload::make('attachment_path')->label('Proposta em PDF')->directory('purchasing/quotations')->acceptedFileTypes(['application/pdf'])->maxSize(10240)->columnSpan(2),
                    Textarea::make('notes')->label('Observações')->rows(4)->columnSpanFull(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('quotationRequest.number')->label('Cotação')->searchable()->sortable(),
            TextColumn::make('supplier.trade_name')->label('Fornecedor')->searchable()->weight('bold'),
            TextColumn::make('proposal_number')->label('Proposta')->placeholder('—')->searchable(),
            TextColumn::make('delivery_days')->label('Entrega')->suffix(' dias')->placeholder('—')->sortable(),
            TextColumn::make('payment_terms')->label('Pagamento')->limit(30)->placeholder('—'),
            TextColumn::make('total_amount')->label('Total')->money('BRL')->sortable(),
            IconColumn::make('is_winner')->label('Vencedora')->boolean(),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn(string $s)=>match($s){'invited'=>'Convidado','viewed'=>'Visualizada','answered'=>'Respondida','declined'=>'Recusada','disqualified'=>'Desclassificada','winner'=>'Vencedora',default=>$s})->color(fn(string $s)=>match($s){'winner'=>'success','declined','disqualified'=>'danger','answered'=>'info','viewed'=>'warning',default=>'gray'}),
        ])->filters([
            SelectFilter::make('status')->label('Status')->options(['invited'=>'Convidado','viewed'=>'Visualizada','answered'=>'Respondida','declined'=>'Recusada','disqualified'=>'Desclassificada','winner'=>'Vencedora']),
        ])->recordActions([EditAction::make()->label('Editar')])
          ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionadas')])])
          ->defaultSort('created_at','desc');
    }

    public static function getPages(): array
    {
        return ['index'=>ListQuotationSuppliers::route('/'),'create'=>CreateQuotationSupplier::route('/criar'),'edit'=>EditQuotationSupplier::route('/{record}/editar')];
    }
}

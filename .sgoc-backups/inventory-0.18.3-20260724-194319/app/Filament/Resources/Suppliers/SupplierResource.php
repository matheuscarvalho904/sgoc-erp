<?php

declare(strict_types=1);

namespace App\Filament\Resources\Suppliers;

use App\Filament\Resources\Suppliers\Pages\{CreateSupplier, EditSupplier, ListSuppliers};
use App\Modules\Purchasing\Domain\Models\Supplier;
use App\Modules\Foundation\Domain\Models\{Organization, Tenant};
use App\Services\BrazilianDocumentLookupService;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\{Hidden, Select, Textarea, TextInput};
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\{Grid, Section, Tabs, Tabs\Tab};
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use RuntimeException;
use UnitEnum;

final class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;
    protected static ?string $recordTitleAttribute = 'trade_name';
    protected static ?string $modelLabel = 'fornecedor';
    protected static ?string $pluralModelLabel = 'fornecedores';
    protected static ?string $navigationLabel = 'Fornecedores';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';
    protected static string|UnitEnum|null $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 20;
    protected static ?string $slug = 'compras/fornecedores';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn () => Tenant::query()->value('id'))->required(),
            Hidden::make('organization_id')->default(fn () => Organization::query()->value('id')),
            Hidden::make('external_data'), Hidden::make('external_data_synced_at'),
            Tabs::make('Fornecedor')->columnSpanFull()->tabs([
                Tab::make('Dados gerais')->schema([
                    Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([
                        TextInput::make('code')->label('Código')->required()->maxLength(30),
                        Select::make('person_type')->label('Tipo de pessoa')->options(['company'=>'Pessoa jurídica','individual'=>'Pessoa física'])->default('company')->live()->required(),
                        TextInput::make('document')->label(fn (callable $get) => $get('person_type') === 'individual' ? 'CPF' : 'CNPJ')->placeholder('00.000.000/0000-00')->maxLength(18)->unique(ignoreRecord:true)->live(onBlur:true)
                            ->afterStateUpdated(fn (?string $state, callable $set, callable $get) => self::lookupCnpj($state,$set,$get)),
                        Select::make('status')->label('Status')->options(['active'=>'Ativo','inactive'=>'Inativo','blocked'=>'Bloqueado'])->default('active')->required(),
                        TextInput::make('legal_name')->label(fn (callable $get) => $get('person_type') === 'individual' ? 'Nome completo' : 'Razão social')->columnSpan(2)->required(),
                        TextInput::make('trade_name')->label(fn (callable $get) => $get('person_type') === 'individual' ? 'Nome de exibição' : 'Nome fantasia')->columnSpan(2)->required(),
                        TextInput::make('state_registration')->label('Inscrição estadual'), TextInput::make('municipal_registration')->label('Inscrição municipal'),
                        TextInput::make('email')->label('E-mail')->email(), TextInput::make('phone')->label('Telefone')->tel(),
                    ]),
                ]),
                Tab::make('Endereço')->schema([Section::make('Localização')->columns(12)->schema([
                    TextInput::make('zip_code')->label('CEP')->placeholder('00000-000')->maxLength(9)->columnSpan(2)->live(onBlur:true)->afterStateUpdated(fn (?string $state, callable $set, callable $get) => self::lookupCep($state,$set,$get)),
                    TextInput::make('street')->label('Logradouro')->columnSpan(5), TextInput::make('number')->label('Número')->columnSpan(2), TextInput::make('complement')->label('Complemento')->columnSpan(3),
                    TextInput::make('district')->label('Bairro')->columnSpan(3), TextInput::make('city')->label('Cidade')->columnSpan(5), TextInput::make('state')->label('UF')->maxLength(2)->columnSpan(2),
                ])]),
                Tab::make('Comercial')->schema([Textarea::make('payment_notes')->label('Condições e observações de pagamento')->rows(5)]),
            ]),
        ]);
    }

    private static function lookupCnpj(?string $state, callable $set, callable $get): void
    {
        if (blank($state) || $get('person_type') !== 'company') return;
        try {
            $data=app(BrazilianDocumentLookupService::class)->lookupCnpj($state);
            foreach (['document','legal_name','trade_name','email','phone','state_registration','zip_code','street','number','complement','district','city','state'] as $field) {
                $source=$field==='trade_name'?'name':$field;
                if (filled($data[$source]??null) && ($field==='document' || blank($get($field)))) $set($field,$data[$source]);
            }
            $set('external_data',$data['external_data']??[]); $set('external_data_synced_at',now());
            Notification::make()->title('CNPJ localizado')->body('Dados do fornecedor preenchidos. Campos já informados foram preservados.')->success()->send();
        } catch (RuntimeException $e) { Notification::make()->title('Consulta de CNPJ')->body($e->getMessage())->warning()->send(); }
    }

    private static function lookupCep(?string $state, callable $set, callable $get): void
    {
        if (blank($state)) return;
        try { $data=app(BrazilianDocumentLookupService::class)->lookupCep($state); foreach (['zip_code','street','district','city','state'] as $field) if (filled($data[$field]??null) && ($field==='zip_code'||blank($get($field)))) $set($field,$data[$field]); }
        catch (RuntimeException $e) { Notification::make()->title('Consulta de CEP')->body($e->getMessage())->warning()->send(); }
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Código')->searchable()->sortable(), TextColumn::make('trade_name')->label('Fornecedor')->searchable()->sortable()->weight('bold'),
            TextColumn::make('document')->label('CPF/CNPJ')->searchable(), TextColumn::make('city')->label('Cidade')->searchable(), TextColumn::make('state')->label('UF'),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn(string $state)=>match($state){'active'=>'Ativo','inactive'=>'Inativo','blocked'=>'Bloqueado',default=>$state})->color(fn(string $state)=>match($state){'active'=>'success','blocked'=>'danger',default=>'gray'}),
        ])->filters([SelectFilter::make('status')->options(['active'=>'Ativo','inactive'=>'Inativo','blocked'=>'Bloqueado'])])->recordActions([EditAction::make()->label('Editar')]);
    }

    public static function getPages(): array { return ['index'=>ListSuppliers::route('/'),'create'=>CreateSupplier::route('/criar'),'edit'=>EditSupplier::route('/{record}/editar')]; }
}

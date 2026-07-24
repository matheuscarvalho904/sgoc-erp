<?php

declare(strict_types=1);

namespace App\Filament\Resources\Suppliers;

use App\Filament\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Modules\Foundation\Domain\Models\Organization;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Purchasing\Domain\Models\Supplier;
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
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
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
            Tabs::make('Fornecedor')->columnSpanFull()->tabs([
                Tab::make('Dados gerais')->schema([
                    Grid::make(['default' => 1, 'md' => 2, 'xl' => 4])->schema([
                        TextInput::make('code')->label('Código')->required()->maxLength(30),
                        Select::make('person_type')->label('Tipo')->default('company')->required()->options(['company' => 'Pessoa jurídica', 'individual' => 'Pessoa física']),
                        TextInput::make('document')->label('CPF/CNPJ')->maxLength(20),
                        Select::make('status')->label('Status')->default('active')->required()->options(['active' => 'Ativo', 'inactive' => 'Inativo', 'blocked' => 'Bloqueado']),
                        TextInput::make('trade_name')->label('Nome fantasia / Nome')->required()->maxLength(200)->columnSpan(2),
                        TextInput::make('legal_name')->label('Razão social')->maxLength(200)->columnSpan(2),
                        TextInput::make('state_registration')->label('Inscrição estadual')->maxLength(40),
                        TextInput::make('municipal_registration')->label('Inscrição municipal')->maxLength(40),
                        TextInput::make('email')->label('E-mail')->email()->maxLength(180),
                        TextInput::make('phone')->label('Telefone')->tel()->maxLength(30),
                    ]),
                ]),
                Tab::make('Endereço')->schema([
                    Section::make('Localização')->columns(12)->schema([
                        TextInput::make('zip_code')->label('CEP')->maxLength(12)->columnSpan(2),
                        TextInput::make('street')->label('Logradouro')->maxLength(180)->columnSpan(5),
                        TextInput::make('number')->label('Número')->maxLength(30)->columnSpan(2),
                        TextInput::make('complement')->label('Complemento')->maxLength(120)->columnSpan(3),
                        TextInput::make('district')->label('Bairro')->maxLength(120)->columnSpan(3),
                        TextInput::make('city')->label('Cidade')->maxLength(120)->columnSpan(5),
                        TextInput::make('state')->label('UF')->maxLength(2)->columnSpan(2),
                    ]),
                ]),
                Tab::make('Comercial')->schema([Textarea::make('payment_notes')->label('Condições e observações de pagamento')->rows(5)]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Código')->searchable()->sortable(),
            TextColumn::make('trade_name')->label('Fornecedor')->searchable()->sortable()->weight('bold'),
            TextColumn::make('document')->label('CPF/CNPJ')->searchable(),
            TextColumn::make('city')->label('Cidade')->searchable()->toggleable(),
            TextColumn::make('state')->label('UF')->toggleable(),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (string $state) => match($state) {'active'=>'Ativo','inactive'=>'Inativo','blocked'=>'Bloqueado',default=>$state})->color(fn (string $state) => match($state) {'active'=>'success','blocked'=>'danger',default=>'gray'}),
        ])->filters([SelectFilter::make('status')->options(['active'=>'Ativo','inactive'=>'Inativo','blocked'=>'Bloqueado'])])
          ->recordActions([EditAction::make()->label('Editar')])
          ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionados')])]);
    }

    public static function getPages(): array
    {
        return ['index'=>ListSuppliers::route('/'),'create'=>CreateSupplier::route('/criar'),'edit'=>EditSupplier::route('/{record}/editar')];
    }
}

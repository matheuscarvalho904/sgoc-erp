<?php

declare(strict_types=1);

namespace App\Filament\Resources\Companies;

use App\Filament\Resources\Companies\Pages\CreateCompany;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\Pages\ListCompanies;
use App\Modules\Foundation\Domain\Models\Company;
use App\Modules\Foundation\Domain\Models\Organization;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Services\BrazilianDocumentLookupService;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use RuntimeException;
use UnitEnum;

final class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'empresa';
    protected static ?string $pluralModelLabel = 'empresas';
    protected static ?string $navigationLabel = 'Empresas';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';
    protected static string|UnitEnum|null $navigationGroup = 'Administração';
    protected static ?int $navigationSort = 10;
    protected static ?string $slug = 'empresas';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn () => Tenant::query()->value('id'))->required(),
            Hidden::make('organization_id')->default(fn () => Organization::query()->value('id'))->required(),

            Tabs::make('Cadastro da empresa')
                ->persistTabInQueryString()
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Dados gerais')
                        ->icon('heroicon-o-building-office-2')
                        ->schema([
                            Grid::make(['default' => 1, 'md' => 2, 'xl' => 4])
                                ->schema([
                                    TextInput::make('document')
                                        ->label('CNPJ')
                                        ->placeholder('00.000.000/0000-00')
                                        ->helperText('Informe o CNPJ e saia do campo para consultar.')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(18)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (?string $state, callable $set): void {
                                            if (blank($state)) {
                                                return;
                                            }

                                            try {
                                                $data = app(BrazilianDocumentLookupService::class)->lookupCnpj($state);

                                                foreach (['document', 'legal_name', 'name', 'email', 'phone', 'state_registration'] as $field) {
                                                    if (filled($data[$field] ?? null)) {
                                                        $set($field, $data[$field]);
                                                    }
                                                }

                                                Notification::make()
                                                    ->title('CNPJ localizado')
                                                    ->body('Os dados da empresa foram preenchidos automaticamente.')
                                                    ->success()
                                                    ->send();
                                            } catch (RuntimeException $exception) {
                                                Notification::make()
                                                    ->title('Consulta de CNPJ')
                                                    ->body($exception->getMessage())
                                                    ->warning()
                                                    ->send();
                                            }
                                        })
                                        ->columnSpan(['default' => 1, 'xl' => 2]),

                                    TextInput::make('code')
                                        ->label('Código')
                                        ->required()
                                        ->maxLength(30),

                                    Select::make('status')
                                        ->label('Status')
                                        ->options(['active' => 'Ativa', 'inactive' => 'Inativa'])
                                        ->default('active')
                                        ->required(),

                                    TextInput::make('legal_name')
                                        ->label('Razão social')
                                        ->required()
                                        ->maxLength(200)
                                        ->columnSpan(['default' => 1, 'xl' => 2]),

                                    TextInput::make('name')
                                        ->label('Nome fantasia')
                                        ->required()
                                        ->maxLength(150)
                                        ->columnSpan(['default' => 1, 'xl' => 2]),

                                    TextInput::make('state_registration')
                                        ->label('Inscrição estadual')
                                        ->maxLength(40),

                                    TextInput::make('municipal_registration')
                                        ->label('Inscrição municipal')
                                        ->maxLength(40),
                                ]),
                        ]),

                    Tab::make('Contato e configuração')
                        ->icon('heroicon-o-phone')
                        ->schema([
                            Section::make('Contato')
                                ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
                                ->schema([
                                    TextInput::make('email')->label('E-mail')->email()->maxLength(180),
                                    TextInput::make('phone')->label('Telefone')->tel()->maxLength(30),
                                    TextInput::make('site')->label('Site')->url()->maxLength(180),
                                ]),

                            Section::make('Configuração')
                                ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
                                ->schema([
                                    TextInput::make('timezone')
                                        ->label('Fuso horário')
                                        ->default('America/Cuiaba')
                                        ->required(),

                                    TextInput::make('currency')
                                        ->label('Moeda')
                                        ->default('BRL')
                                        ->required()
                                        ->maxLength(3),
                                ]),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Código')->searchable()->sortable(),
                TextColumn::make('name')->label('Empresa')->searchable()->sortable()->weight('bold'),
                TextColumn::make('legal_name')->label('Razão social')->searchable()->toggleable(),
                TextColumn::make('document')->label('CNPJ')->searchable(),
                TextColumn::make('email')->label('E-mail')->searchable()->toggleable(),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'active' ? 'Ativa' : 'Inativa')
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),
                TextColumn::make('created_at')->label('Cadastro')->dateTime('d/m/Y H:i')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(['active' => 'Ativa', 'inactive' => 'Inativa']),
            ])
            ->recordActions([EditAction::make()->label('Editar')])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Excluir selecionadas'),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanies::route('/'),
            'create' => CreateCompany::route('/criar'),
            'edit' => EditCompany::route('/{record}/editar'),
        ];
    }
}

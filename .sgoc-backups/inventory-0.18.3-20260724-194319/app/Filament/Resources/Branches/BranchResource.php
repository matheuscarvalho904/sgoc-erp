<?php

declare(strict_types=1);

namespace App\Filament\Resources\Branches;

use App\Filament\Resources\Branches\Pages\CreateBranch;
use App\Filament\Resources\Branches\Pages\EditBranch;
use App\Filament\Resources\Branches\Pages\ListBranches;
use App\Modules\Foundation\Domain\Models\Branch;
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
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use RuntimeException;
use UnitEnum;

final class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'filial';
    protected static ?string $pluralModelLabel = 'filiais';
    protected static ?string $navigationLabel = 'Filiais';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static string|UnitEnum|null $navigationGroup = 'Administração';
    protected static ?int $navigationSort = 20;
    protected static ?string $slug = 'filiais';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Hidden::make('tenant_id')
                    ->default(fn () => Tenant::query()->value('id'))
                    ->required(),

                Hidden::make('organization_id')
                    ->default(fn () => Organization::query()->value('id'))
                    ->required(),

                Tabs::make('Cadastro da filial')
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Dados gerais')
                            ->icon('heroicon-o-building-office-2')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'md' => 2,
                                    'xl' => 4,
                                ])->schema([
                                    Select::make('company_id')
                                        ->label('Empresa')
                                        ->options(fn () => Company::query()
                                            ->where('status', 'active')
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->all())
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->columnSpan([
                                            'default' => 1,
                                            'xl' => 2,
                                        ]),

                                    TextInput::make('code')
                                        ->label('Código da filial')
                                        ->required()
                                        ->maxLength(30),

                                    Toggle::make('is_headquarters')
                                        ->label('Esta filial é a matriz')
                                        ->inline(false)
                                        ->default(false),

                                    TextInput::make('document')
                                        ->label('CNPJ da filial')
                                        ->placeholder('00.000.000/0000-00')
                                        ->helperText('Informe o CNPJ e saia do campo para consultar.')
                                        ->maxLength(18)
                                        ->unique(ignoreRecord: true)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (?string $state, callable $set): void {
                                            if (blank($state)) {
                                                return;
                                            }

                                            try {
                                                $data = app(BrazilianDocumentLookupService::class)->lookupCnpj($state);

                                                foreach ([
                                                    'document',
                                                    'name',
                                                    'email',
                                                    'phone',
                                                    'zip_code',
                                                    'street',
                                                    'number',
                                                    'complement',
                                                    'district',
                                                    'city',
                                                    'state',
                                                ] as $field) {
                                                    if (filled($data[$field] ?? null)) {
                                                        $set($field, $data[$field]);
                                                    }
                                                }

                                                Notification::make()
                                                    ->title('CNPJ localizado')
                                                    ->body('Os dados da filial foram preenchidos automaticamente.')
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
                                        ->columnSpan([
                                            'default' => 1,
                                            'xl' => 2,
                                        ]),

                                    TextInput::make('name')
                                        ->label('Nome da filial')
                                        ->required()
                                        ->maxLength(150)
                                        ->columnSpan([
                                            'default' => 1,
                                            'xl' => 2,
                                        ]),

                                    Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'active' => 'Ativa',
                                            'inactive' => 'Inativa',
                                        ])
                                        ->default('active')
                                        ->required(),
                                ]),

                                Section::make('Contato')
                                    ->icon('heroicon-o-phone')
                                    ->compact()
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                        'xl' => 3,
                                    ])
                                    ->schema([
                                        TextInput::make('email')
                                            ->label('E-mail')
                                            ->email()
                                            ->maxLength(180),

                                        TextInput::make('phone')
                                            ->label('Telefone')
                                            ->tel()
                                            ->maxLength(30),

                                        TextInput::make('site')
                                            ->label('Site')
                                            ->url()
                                            ->maxLength(180),
                                    ]),
                            ]),

                        Tab::make('Endereço')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Section::make('Endereço da filial')
                                    ->description('O CEP preenche automaticamente logradouro, bairro, cidade e UF.')
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                        'xl' => 12,
                                    ])
                                    ->schema([
                                        TextInput::make('zip_code')
                                            ->label('CEP')
                                            ->placeholder('00000-000')
                                            ->maxLength(9)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (?string $state, callable $set): void {
                                                if (blank($state)) {
                                                    return;
                                                }

                                                try {
                                                    $data = app(BrazilianDocumentLookupService::class)->lookupCep($state);

                                                    foreach (['zip_code', 'street', 'district', 'city', 'state'] as $field) {
                                                        if (filled($data[$field] ?? null)) {
                                                            $set($field, $data[$field]);
                                                        }
                                                    }

                                                    Notification::make()
                                                        ->title('CEP localizado')
                                                        ->body('O endereço foi preenchido automaticamente.')
                                                        ->success()
                                                        ->send();
                                                } catch (RuntimeException $exception) {
                                                    Notification::make()
                                                        ->title('Consulta de CEP')
                                                        ->body($exception->getMessage())
                                                        ->warning()
                                                        ->send();
                                                }
                                            })
                                            ->columnSpan([
                                                'default' => 1,
                                                'xl' => 2,
                                            ]),

                                        TextInput::make('street')
                                            ->label('Logradouro')
                                            ->maxLength(180)
                                            ->columnSpan([
                                                'default' => 1,
                                                'xl' => 5,
                                            ]),

                                        TextInput::make('number')
                                            ->label('Número')
                                            ->maxLength(30)
                                            ->columnSpan([
                                                'default' => 1,
                                                'xl' => 2,
                                            ]),

                                        TextInput::make('complement')
                                            ->label('Complemento')
                                            ->maxLength(120)
                                            ->columnSpan([
                                                'default' => 1,
                                                'xl' => 3,
                                            ]),

                                        TextInput::make('district')
                                            ->label('Bairro')
                                            ->maxLength(120)
                                            ->columnSpan([
                                                'default' => 1,
                                                'xl' => 3,
                                            ]),

                                        TextInput::make('city')
                                            ->label('Cidade')
                                            ->maxLength(120)
                                            ->columnSpan([
                                                'default' => 1,
                                                'xl' => 4,
                                            ]),

                                        Select::make('state')
                                            ->label('UF')
                                            ->searchable()
                                            ->options([
                                                'AC' => 'Acre',
                                                'AL' => 'Alagoas',
                                                'AP' => 'Amapá',
                                                'AM' => 'Amazonas',
                                                'BA' => 'Bahia',
                                                'CE' => 'Ceará',
                                                'DF' => 'Distrito Federal',
                                                'ES' => 'Espírito Santo',
                                                'GO' => 'Goiás',
                                                'MA' => 'Maranhão',
                                                'MT' => 'Mato Grosso',
                                                'MS' => 'Mato Grosso do Sul',
                                                'MG' => 'Minas Gerais',
                                                'PA' => 'Pará',
                                                'PB' => 'Paraíba',
                                                'PR' => 'Paraná',
                                                'PE' => 'Pernambuco',
                                                'PI' => 'Piauí',
                                                'RJ' => 'Rio de Janeiro',
                                                'RN' => 'Rio Grande do Norte',
                                                'RS' => 'Rio Grande do Sul',
                                                'RO' => 'Rondônia',
                                                'RR' => 'Roraima',
                                                'SC' => 'Santa Catarina',
                                                'SP' => 'São Paulo',
                                                'SE' => 'Sergipe',
                                                'TO' => 'Tocantins',
                                            ])
                                            ->columnSpan([
                                                'default' => 1,
                                                'xl' => 2,
                                            ]),

                                        TextInput::make('ibge_code')
                                            ->label('Código IBGE')
                                            ->maxLength(15)
                                            ->columnSpan([
                                                'default' => 1,
                                                'xl' => 3,
                                            ]),
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
                TextColumn::make('name')->label('Filial')->searchable()->sortable()->weight('bold'),
                TextColumn::make('company.name')->label('Empresa')->searchable()->sortable(),
                TextColumn::make('document')->label('CNPJ')->searchable()->toggleable(),
                TextColumn::make('city')->label('Cidade')->searchable(),
                TextColumn::make('state')->label('UF'),
                IconColumn::make('is_headquarters')->label('Matriz')->boolean(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'active' ? 'Ativa' : 'Inativa')
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->options(fn () => Company::query()->orderBy('name')->pluck('name', 'id')->all()),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Ativa',
                        'inactive' => 'Inativa',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
            ])
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
            'index' => ListBranches::route('/'),
            'create' => CreateBranch::route('/criar'),
            'edit' => EditBranch::route('/{record}/editar'),
        ];
    }
}

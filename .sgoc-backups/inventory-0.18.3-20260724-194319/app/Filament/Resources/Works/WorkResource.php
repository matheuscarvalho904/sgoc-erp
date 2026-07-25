<?php

declare(strict_types=1);

namespace App\Filament\Resources\Works;

use App\Filament\Resources\Works\Pages\CreateWork;
use App\Filament\Resources\Works\Pages\EditWork;
use App\Filament\Resources\Works\Pages\ListWorks;
use App\Modules\Foundation\Domain\Models\Branch;
use App\Modules\Foundation\Domain\Models\Company;
use App\Modules\Foundation\Domain\Models\Organization;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Foundation\Domain\Models\Work;
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
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class WorkResource extends Resource
{
    protected static ?string $model = Work::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'obra';
    protected static ?string $pluralModelLabel = 'obras';
    protected static ?string $navigationLabel = 'Obras';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';
    protected static string|UnitEnum|null $navigationGroup = 'Administração';
    protected static ?int $navigationSort = 30;
    protected static ?string $slug = 'obras';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn () => Tenant::query()->value('id'))->required(),
            Hidden::make('organization_id')->default(fn () => Organization::query()->value('id'))->required(),

            Tabs::make('Cadastro da obra')
                ->persistTabInQueryString()
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Dados gerais')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->schema([
                            Grid::make(['default' => 1, 'md' => 2, 'xl' => 4])
                                ->schema([
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
                                        ->live(),

                                    Select::make('branch_id')
                                        ->label('Filial')
                                        ->options(fn ($get) => Branch::query()
                                            ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                                            ->where('status', 'active')
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->all())
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    TextInput::make('code')->label('Código')->required()->maxLength(40),

                                    Select::make('status')
                                        ->label('Status')
                                        ->required()
                                        ->default('planning')
                                        ->options([
                                            'planning' => 'Planejamento',
                                            'mobilizing' => 'Mobilização',
                                            'in_progress' => 'Em andamento',
                                            'paused' => 'Paralisada',
                                            'completed' => 'Concluída',
                                            'cancelled' => 'Cancelada',
                                        ]),

                                    TextInput::make('name')
                                        ->label('Nome da obra')
                                        ->required()
                                        ->maxLength(200)
                                        ->columnSpan(['default' => 1, 'xl' => 2]),

                                    TextInput::make('client_name')
                                        ->label('Cliente')
                                        ->maxLength(200),

                                    TextInput::make('contract_number')
                                        ->label('Contrato')
                                        ->maxLength(80),

                                    Textarea::make('description')
                                        ->label('Descrição')
                                        ->rows(4)
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Tab::make('Prazo e orçamento')
                        ->icon('heroicon-o-calendar-days')
                        ->schema([
                            Section::make('Cronograma')
                                ->columns(['default' => 1, 'md' => 2, 'xl' => 4])
                                ->schema([
                                    DatePicker::make('start_date')->label('Início'),
                                    DatePicker::make('expected_end_date')->label('Previsão de término'),
                                    DatePicker::make('actual_end_date')->label('Término efetivo'),
                                    TextInput::make('budget_amount')
                                        ->label('Orçamento')
                                        ->numeric()
                                        ->prefix('R$'),
                                ]),
                        ]),

                    Tab::make('Localização')
                        ->icon('heroicon-o-map-pin')
                        ->schema([
                            Section::make('Endereço da obra')
                                ->columns(['default' => 1, 'md' => 2, 'xl' => 12])
                                ->schema([
                                    TextInput::make('zip_code')->label('CEP')->maxLength(12)
                                        ->columnSpan(['default' => 1, 'xl' => 2]),
                                    TextInput::make('street')->label('Logradouro')->maxLength(180)
                                        ->columnSpan(['default' => 1, 'xl' => 5]),
                                    TextInput::make('number')->label('Número')->maxLength(30)
                                        ->columnSpan(['default' => 1, 'xl' => 2]),
                                    TextInput::make('complement')->label('Complemento')->maxLength(120)
                                        ->columnSpan(['default' => 1, 'xl' => 3]),
                                    TextInput::make('district')->label('Bairro')->maxLength(120)
                                        ->columnSpan(['default' => 1, 'xl' => 3]),
                                    TextInput::make('city')->label('Cidade')->maxLength(120)
                                        ->columnSpan(['default' => 1, 'xl' => 4]),
                                    TextInput::make('state')->label('UF')->maxLength(2)
                                        ->columnSpan(['default' => 1, 'xl' => 2]),
                                    TextInput::make('latitude')->label('Latitude')->numeric()
                                        ->columnSpan(['default' => 1, 'xl' => 3]),
                                    TextInput::make('longitude')->label('Longitude')->numeric()
                                        ->columnSpan(['default' => 1, 'xl' => 3]),
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
                TextColumn::make('name')->label('Obra')->searchable()->sortable()->weight('bold')->limit(50),
                TextColumn::make('branch.name')->label('Filial')->searchable(),
                TextColumn::make('client_name')->label('Cliente')->searchable()->toggleable(),
                TextColumn::make('start_date')->label('Início')->date('d/m/Y')->sortable(),
                TextColumn::make('expected_end_date')->label('Previsão')->date('d/m/Y')->sortable(),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'planning' => 'Planejamento',
                        'mobilizing' => 'Mobilização',
                        'in_progress' => 'Em andamento',
                        'paused' => 'Paralisada',
                        'completed' => 'Concluída',
                        'cancelled' => 'Cancelada',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'in_progress' => 'success',
                        'planning', 'mobilizing' => 'info',
                        'paused' => 'warning',
                        'completed' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options([
                    'planning' => 'Planejamento',
                    'mobilizing' => 'Mobilização',
                    'in_progress' => 'Em andamento',
                    'paused' => 'Paralisada',
                    'completed' => 'Concluída',
                    'cancelled' => 'Cancelada',
                ]),
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
            'index' => ListWorks::route('/'),
            'create' => CreateWork::route('/criar'),
            'edit' => EditWork::route('/{record}/editar'),
        ];
    }
}

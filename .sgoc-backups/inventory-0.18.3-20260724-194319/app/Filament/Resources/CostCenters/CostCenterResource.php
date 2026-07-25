<?php

declare(strict_types=1);

namespace App\Filament\Resources\CostCenters;

use App\Filament\Resources\CostCenters\Pages\CreateCostCenter;
use App\Filament\Resources\CostCenters\Pages\EditCostCenter;
use App\Filament\Resources\CostCenters\Pages\ListCostCenters;
use App\Modules\Foundation\Domain\Models\Branch;
use App\Modules\Foundation\Domain\Models\Company;
use App\Modules\Foundation\Domain\Models\CostCenter;
use App\Modules\Foundation\Domain\Models\Organization;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Foundation\Domain\Models\Work;
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

final class CostCenterResource extends Resource
{
    protected static ?string $model = CostCenter::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'centro de custo';
    protected static ?string $pluralModelLabel = 'centros de custo';
    protected static ?string $navigationLabel = 'Centros de Custo';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';
    protected static string|UnitEnum|null $navigationGroup = 'Administração';
    protected static ?int $navigationSort = 50;
    protected static ?string $slug = 'centros-de-custo';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn () => Tenant::query()->value('id'))->required(),
            Hidden::make('organization_id')->default(fn () => Organization::query()->value('id')),

            Section::make('Cadastro do centro de custo')
                ->icon('heroicon-o-calculator')
                ->description('Estruture os centros de custo administrativos, operacionais e de obras.')
                ->columnSpanFull()
                ->schema([
                    Grid::make(['default' => 1, 'md' => 2, 'xl' => 4])
                        ->schema([
                            Select::make('company_id')
                                ->label('Empresa')
                                ->options(fn () => Company::query()->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->preload(),

                            Select::make('branch_id')
                                ->label('Filial')
                                ->options(fn () => Branch::query()->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->preload(),

                            Select::make('work_id')
                                ->label('Obra')
                                ->options(fn () => Work::query()->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->preload(),

                            Select::make('parent_id')
                                ->label('Centro superior')
                                ->options(fn () => CostCenter::query()->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->preload(),

                            TextInput::make('code')
                                ->label('Código')
                                ->required()
                                ->maxLength(40),

                            TextInput::make('name')
                                ->label('Nome do centro de custo')
                                ->required()
                                ->maxLength(160)
                                ->columnSpan(['default' => 1, 'xl' => 2]),

                            Select::make('type')
                                ->label('Tipo')
                                ->required()
                                ->default('administrative')
                                ->options([
                                    'administrative' => 'Administrativo',
                                    'operational' => 'Operacional',
                                    'work' => 'Obra',
                                    'production' => 'Produção',
                                    'maintenance' => 'Manutenção',
                                ]),

                            Select::make('status')
                                ->label('Status')
                                ->required()
                                ->default('active')
                                ->options(['active' => 'Ativo', 'inactive' => 'Inativo']),

                            Textarea::make('description')
                                ->label('Descrição')
                                ->rows(4)
                                ->columnSpanFull(),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Código')->searchable()->sortable(),
                TextColumn::make('name')->label('Centro de custo')->searchable()->sortable()->weight('bold'),
                TextColumn::make('type')->label('Tipo')->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'administrative' => 'Administrativo',
                        'operational' => 'Operacional',
                        'work' => 'Obra',
                        'production' => 'Produção',
                        'maintenance' => 'Manutenção',
                        default => $state,
                    }),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'active' ? 'Ativo' : 'Inativo')
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Tipo')->options([
                    'administrative' => 'Administrativo',
                    'operational' => 'Operacional',
                    'work' => 'Obra',
                    'production' => 'Produção',
                    'maintenance' => 'Manutenção',
                ]),
                SelectFilter::make('status')->label('Status')->options(['active' => 'Ativo', 'inactive' => 'Inativo']),
            ])
            ->recordActions([EditAction::make()->label('Editar')])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Excluir selecionados'),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCostCenters::route('/'),
            'create' => CreateCostCenter::route('/criar'),
            'edit' => EditCostCenter::route('/{record}/editar'),
        ];
    }
}

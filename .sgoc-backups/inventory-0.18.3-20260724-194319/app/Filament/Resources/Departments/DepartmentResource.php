<?php

declare(strict_types=1);

namespace App\Filament\Resources\Departments;

use App\Filament\Resources\Departments\Pages\CreateDepartment;
use App\Filament\Resources\Departments\Pages\EditDepartment;
use App\Filament\Resources\Departments\Pages\ListDepartments;
use App\Modules\Foundation\Domain\Models\Branch;
use App\Modules\Foundation\Domain\Models\Company;
use App\Modules\Foundation\Domain\Models\Department;
use App\Modules\Foundation\Domain\Models\Organization;
use App\Modules\Foundation\Domain\Models\Tenant;
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

final class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'departamento';
    protected static ?string $pluralModelLabel = 'departamentos';
    protected static ?string $navigationLabel = 'Departamentos';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static string|UnitEnum|null $navigationGroup = 'Administração';
    protected static ?int $navigationSort = 40;
    protected static ?string $slug = 'departamentos';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')->default(fn () => Tenant::query()->value('id'))->required(),
            Hidden::make('organization_id')->default(fn () => Organization::query()->value('id')),

            Section::make('Cadastro do departamento')
                ->icon('heroicon-o-users')
                ->description('Organize a estrutura administrativa da empresa.')
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

                            Select::make('parent_id')
                                ->label('Departamento superior')
                                ->options(fn () => Department::query()->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->preload(),

                            Select::make('status')
                                ->label('Status')
                                ->required()
                                ->default('active')
                                ->options(['active' => 'Ativo', 'inactive' => 'Inativo']),

                            TextInput::make('code')->label('Código')->required()->maxLength(40),

                            TextInput::make('name')
                                ->label('Nome do departamento')
                                ->required()
                                ->maxLength(160)
                                ->columnSpan(['default' => 1, 'xl' => 3]),

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
                TextColumn::make('name')->label('Departamento')->searchable()->sortable()->weight('bold'),
                TextColumn::make('company.name')->label('Empresa')->toggleable(),
                TextColumn::make('branch.name')->label('Filial')->toggleable(),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'active' ? 'Ativo' : 'Inativo')
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),
            ])
            ->filters([
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
            'index' => ListDepartments::route('/'),
            'create' => CreateDepartment::route('/criar'),
            'edit' => EditDepartment::route('/{record}/editar'),
        ];
    }
}

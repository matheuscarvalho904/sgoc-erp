<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Modules\Identity\Domain\Models\Permission;
use App\Modules\Identity\Domain\Models\Role;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
use UnitEnum;

final class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'perfil';
    protected static ?string $pluralModelLabel = 'perfis';
    protected static ?string $navigationLabel = 'Perfis';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static string|UnitEnum|null $navigationGroup = 'Acesso e Segurança';
    protected static ?int $navigationSort = 20;
    protected static ?string $slug = 'perfis';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Hidden::make('tenant_id')
                ->default(fn () => Tenant::query()->value('id'))
                ->required(),

            Tabs::make('Cadastro do perfil')
                ->persistTabInQueryString()
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Dados gerais')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Section::make('Identificação')
                                ->schema([
                                    Grid::make(['default' => 1, 'md' => 2, 'xl' => 4])
                                        ->schema([
                                            TextInput::make('code')
                                                ->label('Código')
                                                ->required()
                                                ->maxLength(80),

                                            TextInput::make('name')
                                                ->label('Nome do perfil')
                                                ->required()
                                                ->maxLength(150)
                                                ->columnSpan(['default' => 1, 'xl' => 2]),

                                            Select::make('status')
                                                ->label('Status')
                                                ->options([
                                                    'active' => 'Ativo',
                                                    'inactive' => 'Inativo',
                                                ])
                                                ->default('active')
                                                ->required(),

                                            Toggle::make('is_super_admin')
                                                ->label('Superadministrador')
                                                ->helperText('Concede acesso total ao sistema.'),

                                            Toggle::make('is_system')
                                                ->label('Perfil do sistema')
                                                ->helperText('Perfis do sistema devem ser alterados com cautela.'),

                                            Textarea::make('description')
                                                ->label('Descrição')
                                                ->rows(4)
                                                ->columnSpanFull(),
                                        ]),
                                ]),
                        ]),

                    Tab::make('Permissões')
                        ->icon('heroicon-o-key')
                        ->schema([
                            Section::make('Permissões do perfil')
                                ->description('Selecione as permissões liberadas para este perfil.')
                                ->schema([
                                    CheckboxList::make('permission_ids')
                                        ->label('Permissões')
                                        ->options(fn (): array => Permission::query()
                                            ->orderBy('module')
                                            ->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(fn (Permission $permission): array => [
                                                (string) $permission->getKey() =>
                                                    mb_strtoupper((string) $permission->module) . ' — ' . $permission->name,
                                            ])
                                            ->all())
                                        ->searchable()
                                        ->bulkToggleable()
                                        ->columns([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ])
                                        ->dehydrated(false)
                                        ->afterStateHydrated(function (CheckboxList $component, ?Role $record): void {
                                            if (! $record) {
                                                return;
                                            }

                                            $component->state(
                                                $record->permissions()
                                                    ->wherePivot('granted', true)
                                                    ->pluck('access_control.permissions.id')
                                                    ->map(fn ($id): string => (string) $id)
                                                    ->all()
                                            );
                                        }),
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
                TextColumn::make('name')->label('Perfil')->searchable()->sortable()->weight('bold'),
                TextColumn::make('permissions_count')->label('Permissões')->counts('permissions')->sortable(),
                IconColumn::make('is_super_admin')->label('Super Admin')->boolean(),
                IconColumn::make('is_system')->label('Sistema')->boolean(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'active' ? 'Ativo' : 'Inativo')
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
            ])
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
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/criar'),
            'edit' => EditRole::route('/{record}/editar'),
        ];
    }
}

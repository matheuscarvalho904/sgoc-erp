<?php

declare(strict_types=1);

namespace App\Filament\Resources\Permissions;

use App\Filament\Resources\Permissions\Pages\EditPermission;
use App\Filament\Resources\Permissions\Pages\ListPermissions;
use App\Modules\Identity\Domain\Models\Permission;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'permissão';
    protected static ?string $pluralModelLabel = 'permissões';
    protected static ?string $navigationLabel = 'Permissões';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';
    protected static string|UnitEnum|null $navigationGroup = 'Acesso e Segurança';
    protected static ?int $navigationSort = 30;
    protected static ?string $slug = 'permissoes';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Detalhes da permissão')
                ->description('Permissões do sistema são geradas por módulo e ação.')
                ->columnSpanFull()
                ->schema([
                    Grid::make(['default' => 1, 'md' => 2, 'xl' => 4])
                        ->schema([
                            TextInput::make('code')
                                ->label('Código')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('name')
                                ->label('Nome')
                                ->required()
                                ->maxLength(180)
                                ->columnSpan(['default' => 1, 'xl' => 2]),

                            TextInput::make('module')
                                ->label('Módulo')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('action')
                                ->label('Ação')
                                ->disabled()
                                ->dehydrated(false),

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
                TextColumn::make('module')->label('Módulo')->badge()->searchable()->sortable(),
                TextColumn::make('name')->label('Permissão')->searchable()->sortable()->weight('bold'),
                TextColumn::make('action')->label('Ação')->badge()->searchable(),
                TextColumn::make('code')->label('Código')->searchable()->toggleable(),
                IconColumn::make('is_system')->label('Sistema')->boolean(),
            ])
            ->filters([
                SelectFilter::make('module')
                    ->label('Módulo')
                    ->options(fn () => Permission::query()
                        ->select('module')
                        ->distinct()
                        ->orderBy('module')
                        ->pluck('module', 'module')
                        ->all()),
            ])
            ->recordActions([
                EditAction::make()->label('Editar descrição'),
            ])
            ->defaultSort('module');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'edit' => EditPermission::route('/{record}/editar'),
        ];
    }
}

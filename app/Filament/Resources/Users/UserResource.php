<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use App\Modules\Identity\Domain\Models\Role;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use UnitEnum;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'usuário';
    protected static ?string $pluralModelLabel = 'usuários';
    protected static ?string $navigationLabel = 'Usuários';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static string|UnitEnum|null $navigationGroup = 'Acesso e Segurança';
    protected static ?int $navigationSort = 10;
    protected static ?string $slug = 'usuarios';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Cadastro do usuário')
                ->persistTabInQueryString()
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Dados de acesso')
                        ->icon('heroicon-o-user-circle')
                        ->schema([
                            Section::make('Identificação')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                    'xl' => 2,
                                ])
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Nome')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('email')
                                        ->label('E-mail')
                                        ->email()
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255),

                                    TextInput::make('password')
                                        ->label('Senha')
                                        ->helperText('Use no mínimo 8 caracteres.')
                                        ->password()
                                        ->revealable()
                                        ->required(fn (string $operation): bool => $operation === 'create')
                                        ->dehydrated(fn (?string $state): bool => filled($state))
                                        ->minLength(8)
                                        ->maxLength(255)
                                        ->validationMessages([
                                            'required' => 'Informe a senha.',
                                            'min' => 'A senha deve possuir pelo menos 8 caracteres.',
                                            'max' => 'A senha não pode ultrapassar 255 caracteres.',
                                        ]),

                                    TextInput::make('password_confirmation')
                                        ->label('Confirmar senha')
                                        ->password()
                                        ->revealable()
                                        ->same('password')
                                        ->required(fn (string $operation, callable $get): bool =>
                                            $operation === 'create' || filled($get('password')))
                                        ->dehydrated(false)
                                        ->validationMessages([
                                            'required' => 'Confirme a senha.',
                                            'same' => 'A confirmação deve ser igual à senha.',
                                        ]),
                                ]),
                        ]),

                    Tab::make('Perfis de acesso')
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            Section::make('Perfis vinculados')
                                ->description('Selecione os perfis que definem as permissões do usuário.')
                                ->schema([
                                    CheckboxList::make('role_ids')
                                        ->label('Perfis')
                                        ->options(fn (): array => Role::query()
                                            ->where('status', 'active')
                                            ->orderByDesc('is_super_admin')
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->all())
                                        ->columns([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ])
                                        ->searchable()
                                        ->bulkToggleable()
                                        ->dehydrated(false)
                                        ->afterStateHydrated(function (CheckboxList $component, ?User $record): void {
                                            if (! $record) {
                                                return;
                                            }

                                            $component->state(
                                                DB::table('access_control.user_roles')
                                                    ->where('user_id', $record->getKey())
                                                    ->where('status', 'active')
                                                    ->pluck('role_id')
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
                TextColumn::make('name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles_summary')
                    ->label('Perfis')
                    ->state(function (User $record): string {
                        return DB::table('access_control.user_roles as ur')
                            ->join('access_control.roles as r', 'r.id', '=', 'ur.role_id')
                            ->where('ur.user_id', $record->getKey())
                            ->where('ur.status', 'active')
                            ->orderBy('r.name')
                            ->pluck('r.name')
                            ->implode(', ');
                    })
                    ->badge()
                    ->separator(','),

                TextColumn::make('created_at')
                    ->label('Cadastro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/criar'),
            'edit' => EditUser::route('/{record}/editar'),
        ];
    }
}

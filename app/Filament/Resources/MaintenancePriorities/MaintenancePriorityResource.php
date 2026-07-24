<?php
 declare(strict_types=1);
 namespace App\Filament\Resources\MaintenancePriorities;
 use App\Filament\Resources\MaintenancePriorities\Pages\CreateMaintenancePriority; use App\Filament\Resources\MaintenancePriorities\Pages\EditMaintenancePriority; use App\Filament\Resources\MaintenancePriorities\Pages\ListMaintenancePriorities;
 use App\Modules\Maintenance\Domain\Models\MaintenancePriority; use BackedEnum; use UnitEnum; use Filament\Resources\Resource; use Filament\Schemas\Schema; use Filament\Schemas\Components\Section; use Filament\Schemas\Components\Grid; use Filament\Forms\Components\TextInput; use Filament\Forms\Components\Select; use Filament\Forms\Components\Toggle; use Filament\Forms\Components\DatePicker; use Filament\Tables\Table; use Filament\Tables\Columns\TextColumn; use Filament\Tables\Columns\IconColumn; use Filament\Actions\EditAction; use Filament\Actions\DeleteBulkAction; use Filament\Actions\BulkActionGroup;
 final class MaintenancePriorityResource extends Resource { protected static ?string $model=MaintenancePriority::class; protected static ?string $modelLabel='Prioridade'; protected static ?string $pluralModelLabel='Prioridades'; protected static ?string $navigationLabel='Prioridades'; protected static string|UnitEnum|null $navigationGroup='Oficina e Manutenção'; protected static string|BackedEnum|null $navigationIcon='heroicon-o-wrench-screwdriver';
 public static function form(Schema $schema): Schema { return $schema->columns(1)->components([Section::make('Prioridade')->schema([Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([TextInput::make('code')->label('Code')->required(),
                    TextInput::make('name')->label('Name')->required(),
                    TextInput::make('level')->numeric()->label('Level'),
                    TextInput::make('sla_hours')->numeric()->label('Sla Hours'),
                    TextInput::make('color')->label('Color'),
                    Select::make('status')->label('Status')->options(['active'=>'Ativo','inactive'=>'Inativo'])->default('active')->required()])])]); }
 public static function table(Table $table): Table { return $table->columns([TextColumn::make('code')->label('Code')->searchable()->sortable(),
            TextColumn::make('name')->label('Name')->searchable()->sortable(),
            TextColumn::make('level')->label('Level')->sortable(),
            TextColumn::make('sla_hours')->label('Sla Hours')->sortable(),
            TextColumn::make('color')->label('Color')->searchable()->sortable(),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (?string $state): string => $state === 'active' ? 'Ativo' : 'Inativo')->color(fn (?string $state): string => $state === 'active' ? 'success' : 'gray')])->recordActions([EditAction::make()->label('Editar')])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionados')])]); }
 public static function getPages(): array { return ['index'=>ListMaintenancePriorities::route('/'),'create'=>CreateMaintenancePriority::route('/criar'),'edit'=>EditMaintenancePriority::route('/{record}/editar')]; } }

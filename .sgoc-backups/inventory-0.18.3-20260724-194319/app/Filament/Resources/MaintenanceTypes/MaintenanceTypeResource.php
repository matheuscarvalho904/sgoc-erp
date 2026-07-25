<?php
 declare(strict_types=1);
 namespace App\Filament\Resources\MaintenanceTypes;
 use App\Filament\Resources\MaintenanceTypes\Pages\CreateMaintenanceType; use App\Filament\Resources\MaintenanceTypes\Pages\EditMaintenanceType; use App\Filament\Resources\MaintenanceTypes\Pages\ListMaintenanceTypes;
 use App\Modules\Maintenance\Domain\Models\MaintenanceType; use BackedEnum; use UnitEnum; use Filament\Resources\Resource; use Filament\Schemas\Schema; use Filament\Schemas\Components\Section; use Filament\Schemas\Components\Grid; use Filament\Forms\Components\TextInput; use Filament\Forms\Components\Select; use Filament\Forms\Components\Toggle; use Filament\Forms\Components\DatePicker; use Filament\Tables\Table; use Filament\Tables\Columns\TextColumn; use Filament\Tables\Columns\IconColumn; use Filament\Actions\EditAction; use Filament\Actions\DeleteBulkAction; use Filament\Actions\BulkActionGroup;
 final class MaintenanceTypeResource extends Resource { protected static ?string $model=MaintenanceType::class; protected static ?string $modelLabel='Tipo de manutenção'; protected static ?string $pluralModelLabel='Tipos de manutenção'; protected static ?string $navigationLabel='Tipos de manutenção'; protected static string|UnitEnum|null $navigationGroup='Oficina e Manutenção'; protected static string|BackedEnum|null $navigationIcon='heroicon-o-wrench-screwdriver';
 public static function form(Schema $schema): Schema { return $schema->columns(1)->components([Section::make('Tipo de manutenção')->schema([Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([TextInput::make('code')->label('Code')->required(),
                    TextInput::make('name')->label('Name')->required(),
                    Toggle::make('is_preventive')->label('Is Preventive'),
                    Toggle::make('requires_approval')->label('Requires Approval'),
                    Select::make('status')->label('Status')->options(['active'=>'Ativo','inactive'=>'Inativo'])->default('active')->required()])])]); }
 public static function table(Table $table): Table { return $table->columns([TextColumn::make('code')->label('Code')->searchable()->sortable(),
            TextColumn::make('name')->label('Name')->searchable()->sortable(),
            IconColumn::make('is_preventive')->boolean()->label('Is Preventive'),
            IconColumn::make('requires_approval')->boolean()->label('Requires Approval'),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (?string $state): string => $state === 'active' ? 'Ativo' : 'Inativo')->color(fn (?string $state): string => $state === 'active' ? 'success' : 'gray')])->recordActions([EditAction::make()->label('Editar')])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionados')])]); }
 public static function getPages(): array { return ['index'=>ListMaintenanceTypes::route('/'),'create'=>CreateMaintenanceType::route('/criar'),'edit'=>EditMaintenanceType::route('/{record}/editar')]; } }

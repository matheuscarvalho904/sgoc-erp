<?php
 declare(strict_types=1);
 namespace App\Filament\Resources\MaintenancePlans;
 use App\Filament\Resources\MaintenancePlans\Pages\CreateMaintenancePlan; use App\Filament\Resources\MaintenancePlans\Pages\EditMaintenancePlan; use App\Filament\Resources\MaintenancePlans\Pages\ListMaintenancePlans;
 use App\Modules\Maintenance\Domain\Models\MaintenancePlan; use BackedEnum; use UnitEnum; use Filament\Resources\Resource; use Filament\Schemas\Schema; use Filament\Schemas\Components\Section; use Filament\Schemas\Components\Grid; use Filament\Forms\Components\TextInput; use Filament\Forms\Components\Select; use Filament\Forms\Components\Toggle; use Filament\Forms\Components\DatePicker; use Filament\Tables\Table; use Filament\Tables\Columns\TextColumn; use Filament\Tables\Columns\IconColumn; use Filament\Actions\EditAction; use Filament\Actions\DeleteBulkAction; use Filament\Actions\BulkActionGroup;
 final class MaintenancePlanResource extends Resource { protected static ?string $model=MaintenancePlan::class; protected static ?string $modelLabel='Plano preventivo'; protected static ?string $pluralModelLabel='Planos preventivos'; protected static ?string $navigationLabel='Planos preventivos'; protected static string|UnitEnum|null $navigationGroup='Oficina e Manutenção'; protected static string|BackedEnum|null $navigationIcon='heroicon-o-wrench-screwdriver';
 public static function form(Schema $schema): Schema { return $schema->columns(1)->components([Section::make('Plano preventivo')->schema([Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([TextInput::make('code')->label('Code')->required(),
                    TextInput::make('name')->label('Name')->required(),
                    Select::make('trigger_type')->label('Gatilho')->options(['hourmeter'=>'Horímetro','odometer'=>'Hodômetro','calendar_days'=>'Dias corridos'])->default('hourmeter')->required(),
                    TextInput::make('interval_value')->numeric()->label('Interval Value'),
                    TextInput::make('advance_value')->numeric()->label('Advance Value'),
                    DatePicker::make('next_due_date')->label('Próxima data'),
                    TextInput::make('next_due_meter')->numeric()->label('Next Due Meter'),
                    Toggle::make('auto_create_work_order')->label('Auto Create Work Order'),
                    Select::make('status')->label('Status')->options(['active'=>'Ativo','inactive'=>'Inativo'])->default('active')->required()])])]); }
 public static function table(Table $table): Table { return $table->columns([TextColumn::make('code')->label('Code')->searchable()->sortable(),
            TextColumn::make('name')->label('Name')->searchable()->sortable(),
            TextColumn::make('trigger_type')->label('Gatilho')->formatStateUsing(fn (?string $state): string => match($state){'hourmeter'=>'Horímetro','odometer'=>'Hodômetro','calendar_days'=>'Dias',default=>(string)$state}),
            TextColumn::make('interval_value')->numeric(decimalPlaces: 2)->label('Interval Value'),
            TextColumn::make('advance_value')->numeric(decimalPlaces: 2)->label('Advance Value'),
            TextColumn::make('next_due_date')->label('Próxima data')->date('d/m/Y'),
            TextColumn::make('next_due_meter')->numeric(decimalPlaces: 2)->label('Next Due Meter'),
            IconColumn::make('auto_create_work_order')->boolean()->label('Auto Create Work Order'),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (?string $state): string => $state === 'active' ? 'Ativo' : 'Inativo')->color(fn (?string $state): string => $state === 'active' ? 'success' : 'gray')])->recordActions([EditAction::make()->label('Editar')])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionados')])]); }
 public static function getPages(): array { return ['index'=>ListMaintenancePlans::route('/'),'create'=>CreateMaintenancePlan::route('/criar'),'edit'=>EditMaintenancePlan::route('/{record}/editar')]; } }

<?php

declare(strict_types=1);
namespace App\Filament\Resources\FuelStorages;
use App\Filament\Resources\FuelStorages\Pages\{CreateFuelStorage,EditFuelStorage,ListFuelStorages};
use App\Modules\Assets\Domain\Models\{Asset,Fuel};
use App\Modules\Foundation\Domain\Models\{Branch,Company,CostCenter,Work};
use App\Modules\Fuel\Domain\Models\FuelStorage;
use BackedEnum; use UnitEnum;
use Filament\Actions\EditAction; use Filament\Forms\Components\{Select,TextInput};
use Filament\Resources\Resource; use Filament\Schemas\Components\{Grid,Section}; use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn; use Filament\Tables\Table;
final class FuelStorageResource extends Resource {
 protected static ?string $model=FuelStorage::class; protected static ?string $modelLabel='Ponto de combustível'; protected static ?string $pluralModelLabel='Pontos de combustível'; protected static ?string $navigationLabel='Tanques e Comboios'; protected static string|UnitEnum|null $navigationGroup='Gestão de Combustíveis'; protected static string|BackedEnum|null $navigationIcon='heroicon-o-circle-stack'; protected static ?int $navigationSort=10;
 public static function form(Schema $schema): Schema { return $schema->columns(1)->components([Section::make('Identificação e capacidade')->schema([Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([
 TextInput::make('code')->label('Código')->required(),TextInput::make('name')->label('Nome')->required()->columnSpan(2),Select::make('storage_type')->label('Tipo')->options(['tank'=>'Tanque','truck'=>'Comboio','mobile'=>'Móvel','third_party'=>'Terceiro'])->default('tank')->required(),
 Select::make('company_id')->label('Empresa')->options(fn()=>Company::query()->orderBy('name')->pluck('name','id'))->searchable()->preload(),Select::make('branch_id')->label('Filial')->options(fn()=>Branch::query()->orderBy('name')->pluck('name','id'))->searchable()->preload(),Select::make('work_id')->label('Obra')->options(fn()=>Work::query()->orderBy('name')->pluck('name','id'))->searchable()->preload(),Select::make('cost_center_id')->label('Centro de custo')->options(fn()=>CostCenter::query()->orderBy('name')->pluck('name','id'))->searchable()->preload(),
 Select::make('asset_id')->label('Ativo/Comboio')->options(fn()=>Asset::query()->orderBy('name')->pluck('name','id'))->searchable()->preload(),Select::make('default_fuel_id')->label('Combustível padrão')->options(fn()=>Fuel::query()->where('status','active')->orderBy('name')->pluck('name','id'))->searchable()->preload(),TextInput::make('capacity_liters')->label('Capacidade')->numeric()->suffix('L')->required(),TextInput::make('minimum_level_liters')->label('Estoque mínimo')->numeric()->suffix('L')->default(0),TextInput::make('manufacturer')->label('Fabricante'),TextInput::make('serial_number')->label('Número de série'),TextInput::make('responsible_name')->label('Responsável'),TextInput::make('location')->label('Localização')->columnSpan(2),Select::make('status')->label('Status')->options(['active'=>'Ativo','inactive'=>'Inativo'])->default('active')->required()
 ])])]); }
 public static function table(Table $table): Table { return $table->columns([TextColumn::make('code')->label('Código')->searchable()->sortable(),TextColumn::make('name')->label('Ponto')->searchable()->sortable(),TextColumn::make('storage_type')->label('Tipo')->badge()->formatStateUsing(fn($s)=>match($s){'truck'=>'Comboio','mobile'=>'Móvel','third_party'=>'Terceiro',default=>'Tanque'}),TextColumn::make('capacity_liters')->label('Capacidade')->numeric(decimalPlaces:2)->suffix(' L'),TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn($s)=>$s==='active'?'Ativo':'Inativo')])->recordActions([EditAction::make()->label('Editar')]); }
 public static function getPages(): array { return ['index'=>ListFuelStorages::route('/'),'create'=>CreateFuelStorage::route('/criar'),'edit'=>EditFuelStorage::route('/{record}/editar')]; }
}

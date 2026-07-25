<?php

declare(strict_types=1);
namespace App\Filament\Resources\Fuelings;
use App\Filament\Resources\Fuelings\Pages\{CreateFueling,ListFuelings};
use App\Modules\Assets\Domain\Models\{Asset,Fuel};
use App\Modules\Fuel\Domain\Models\{Fueling,FuelStorage};
use BackedEnum; use UnitEnum;
use Filament\Forms\Components\{DateTimePicker,Hidden,Select,Textarea,TextInput}; use Filament\Resources\Resource; use Filament\Schemas\Components\{Grid,Section}; use Filament\Schemas\Schema; use Filament\Tables\Columns\TextColumn; use Filament\Tables\Filters\SelectFilter; use Filament\Tables\Table;
final class FuelingResource extends Resource {
 protected static ?string $model=Fueling::class; protected static ?string $modelLabel='Abastecimento'; protected static ?string $pluralModelLabel='Abastecimentos'; protected static ?string $navigationLabel='Abastecimentos'; protected static string|UnitEnum|null $navigationGroup='Gestão de Combustíveis'; protected static string|BackedEnum|null $navigationIcon='heroicon-o-truck'; protected static ?int $navigationSort=30;
 public static function form(Schema $schema): Schema { return $schema->columns(1)->components([Section::make('Lançamento do abastecimento')->schema([Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([
 TextInput::make('number')->label('Número')->default(fn()=>'AB-'.now()->format('YmdHis'))->required(),DateTimePicker::make('fueled_at')->label('Data e hora')->default(now())->required(),Select::make('storage_id')->label('Origem')->options(fn()=>FuelStorage::query()->where('status','active')->orderBy('name')->pluck('name','id'))->searchable()->preload()->required(),Select::make('fuel_id')->label('Combustível')->options(fn()=>Fuel::query()->where('status','active')->orderBy('name')->pluck('name','id'))->searchable()->preload()->required(),
 Select::make('asset_id')->label('Veículo/Equipamento')->options(fn()=>Asset::query()->where('status','active')->orderBy('name')->get()->mapWithKeys(fn(Asset $a)=>[$a->id=>trim(($a->code? $a->code.' - ':'').$a->name.($a->plate?' | '.$a->plate:''))]))->searchable()->preload()->required(),TextInput::make('quantity_liters')->label('Litros')->numeric()->suffix('L')->required(),Select::make('meter_type')->label('Medidor')->options(['none'=>'Nenhum','odometer'=>'Hodômetro','hourmeter'=>'Horímetro'])->default('none')->required(),TextInput::make('previous_meter_reading')->label('Leitura anterior')->numeric(),TextInput::make('meter_reading')->label('Leitura atual')->numeric(),TextInput::make('operator_name')->label('Operador/Motorista')->columnSpan(2),Hidden::make('status')->default('posted'),Textarea::make('notes')->label('Observações')->columnSpanFull()
 ])])]); }
 public static function table(Table $table): Table { return $table->columns([TextColumn::make('number')->label('Número')->searchable(),TextColumn::make('fueled_at')->label('Data')->dateTime('d/m/Y H:i')->sortable(),TextColumn::make('asset_id')->label('Ativo'),TextColumn::make('quantity_liters')->label('Litros')->numeric(decimalPlaces:2)->suffix(' L'),TextColumn::make('total_cost')->label('Custo')->money('BRL'),TextColumn::make('calculated_consumption')->label('Consumo')->numeric(decimalPlaces:2)->suffix(fn($record)=>$record->meter_type==='odometer'?' km/L':' L/h')])->filters([SelectFilter::make('storage_id')->label('Ponto')->options(fn()=>FuelStorage::query()->orderBy('name')->pluck('name','id'))])->defaultSort('fueled_at','desc'); }
 public static function getPages(): array { return ['index'=>ListFuelings::route('/'),'create'=>CreateFueling::route('/criar')]; }
}

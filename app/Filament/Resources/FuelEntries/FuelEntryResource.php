<?php

declare(strict_types=1);
namespace App\Filament\Resources\FuelEntries;
use App\Filament\Resources\FuelEntries\Pages\{CreateFuelEntry,ListFuelEntries};
use App\Modules\Assets\Domain\Models\Fuel;
use App\Modules\Fuel\Domain\Models\{FuelEntry,FuelStorage};
use App\Modules\Purchasing\Domain\Models\Supplier;
use BackedEnum; use UnitEnum;
use Filament\Forms\Components\{DatePicker,DateTimePicker,Hidden,Select,Textarea,TextInput}; use Filament\Resources\Resource; use Filament\Schemas\Components\{Grid,Section}; use Filament\Schemas\Schema; use Filament\Tables\Columns\TextColumn; use Filament\Tables\Table;
final class FuelEntryResource extends Resource {
 protected static ?string $model=FuelEntry::class; protected static ?string $modelLabel='Entrada de combustível'; protected static ?string $pluralModelLabel='Entradas de combustível'; protected static ?string $navigationLabel='Entradas de Combustível'; protected static string|UnitEnum|null $navigationGroup='Gestão de Combustíveis'; protected static string|BackedEnum|null $navigationIcon='heroicon-o-arrow-down-tray'; protected static ?int $navigationSort=20;
 public static function form(Schema $schema): Schema { return $schema->columns(1)->components([Section::make('Recebimento')->schema([Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([
 TextInput::make('number')->label('Número')->default(fn()=>'EC-'.now()->format('YmdHis'))->required(),Select::make('storage_id')->label('Destino')->options(fn()=>FuelStorage::query()->where('status','active')->orderBy('name')->pluck('name','id'))->searchable()->preload()->required(),Select::make('fuel_id')->label('Combustível')->options(fn()=>Fuel::query()->where('status','active')->orderBy('name')->pluck('name','id'))->searchable()->preload()->required(),Select::make('supplier_id')->label('Fornecedor')->options(fn()=>Supplier::query()->orderBy('name')->pluck('name','id'))->searchable()->preload(),
 TextInput::make('invoice_number')->label('Nota fiscal'),DatePicker::make('invoice_date')->label('Data da NF'),DateTimePicker::make('received_at')->label('Recebido em')->default(now())->required(),TextInput::make('quantity_liters')->label('Quantidade')->numeric()->suffix('L')->required()->live(onBlur:true),TextInput::make('unit_cost')->label('Custo unitário')->numeric()->prefix('R$')->default(0)->live(onBlur:true),TextInput::make('total_cost')->label('Valor total')->numeric()->prefix('R$')->required(),Hidden::make('status')->default('posted'),Textarea::make('notes')->label('Observações')->columnSpanFull()
 ])])]); }
 public static function table(Table $table): Table { return $table->columns([TextColumn::make('number')->label('Número')->searchable()->sortable(),TextColumn::make('received_at')->label('Recebimento')->dateTime('d/m/Y H:i')->sortable(),TextColumn::make('quantity_liters')->label('Quantidade')->numeric(decimalPlaces:2)->suffix(' L'),TextColumn::make('total_cost')->label('Total')->money('BRL'),TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn($s)=>$s==='posted'?'Lançada':$s)])->defaultSort('received_at','desc'); }
 public static function getPages(): array { return ['index'=>ListFuelEntries::route('/'),'create'=>CreateFuelEntry::route('/criar')]; }
}

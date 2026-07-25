<?php
declare(strict_types=1);
namespace App\Filament\Resources\Warehouses;
use App\Filament\Resources\Warehouses\Pages\{CreateWarehouse,EditWarehouse,ListWarehouses};
use App\Modules\Inventory\Domain\Models\Warehouse;
use BackedEnum; use UnitEnum;
use Filament\Resources\Resource; use Filament\Schemas\Schema; use Filament\Schemas\Components\{Grid,Section};
use Filament\Forms\Components\{Select,TextInput,Toggle}; use Filament\Tables\Table; use Filament\Tables\Columns\{IconColumn,TextColumn};
use Filament\Actions\{BulkActionGroup,DeleteBulkAction,EditAction};
final class WarehouseResource extends Resource {
 protected static ?string $model=Warehouse::class; protected static ?string $modelLabel='Almoxarifado'; protected static ?string $pluralModelLabel='Almoxarifados'; protected static ?string $navigationLabel='Almoxarifados'; protected static string|UnitEnum|null $navigationGroup='Estoque e Almoxarifado'; protected static string|BackedEnum|null $navigationIcon='heroicon-o-building-storefront';
 public static function form(Schema $schema): Schema { return $schema->columns(1)->components([Section::make('Identificação')->schema([Grid::make(['default'=>1,'md'=>2,'xl'=>4])->schema([
 TextInput::make('code')->label('Código')->required()->maxLength(40), TextInput::make('name')->label('Nome')->required()->maxLength(160)->columnSpan(['md'=>1,'xl'=>2]), Select::make('type')->label('Tipo')->options(['general'=>'Geral','workshop'=>'Oficina','work'=>'Obra','fuel'=>'Combustíveis','lubricants'=>'Lubrificantes','precast'=>'Pré-moldados'])->default('general')->required(),
 TextInput::make('responsible_name')->label('Responsável'), TextInput::make('phone')->label('Telefone'), TextInput::make('location')->label('Localização')->columnSpan(['md'=>2]), Toggle::make('allows_negative_stock')->label('Permite estoque negativo')->default(false), Select::make('status')->label('Status')->options(['active'=>'Ativo','inactive'=>'Inativo'])->default('active')->required()
 ])])]); }
 public static function table(Table $table): Table { return $table->columns([TextColumn::make('code')->label('Código')->searchable()->sortable(),TextColumn::make('name')->label('Nome')->searchable()->sortable(),TextColumn::make('type')->label('Tipo')->badge()->formatStateUsing(fn (?string $state): string => match($state){'workshop'=>'Oficina','work'=>'Obra','fuel'=>'Combustíveis','lubricants'=>'Lubrificantes','precast'=>'Pré-moldados',default=>'Geral'}),TextColumn::make('responsible_name')->label('Responsável')->searchable(),IconColumn::make('allows_negative_stock')->label('Negativo')->boolean(),TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (?string $state): string => $state==='active'?'Ativo':'Inativo')->color(fn (?string $state): string => $state==='active'?'success':'gray')])->recordActions([EditAction::make()->label('Editar')])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Excluir selecionados')])]); }
 public static function getPages(): array { return ['index'=>ListWarehouses::route('/'),'create'=>CreateWarehouse::route('/criar'),'edit'=>EditWarehouse::route('/{record}/editar')]; }
}

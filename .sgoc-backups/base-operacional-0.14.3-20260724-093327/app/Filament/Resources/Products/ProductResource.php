<?php
declare(strict_types=1);
namespace App\Filament\Resources\Products;
use App\Filament\Resources\Products\Pages\{CreateProduct,EditProduct,ListProducts};
use App\Modules\Catalog\Domain\Models\{Brand,Product,ProductCategory,Unit}; use App\Modules\Foundation\Domain\Models\Tenant;
use BackedEnum; use UnitEnum; use Filament\Resources\Resource; use Filament\Schemas\Schema; use Filament\Schemas\Components\Section;
use Filament\Forms\Components\{Hidden,Select,TextInput,Textarea,Toggle}; use Filament\Tables\Table; use Filament\Tables\Columns\{IconColumn,TextColumn}; use Filament\Actions\EditAction;
final class ProductResource extends Resource {
 protected static ?string $model=Product::class; protected static ?string $recordTitleAttribute='name'; protected static ?string $modelLabel='produto'; protected static ?string $pluralModelLabel='produtos'; protected static ?string $navigationLabel='Produtos'; protected static string|BackedEnum|null $navigationIcon='heroicon-o-cube'; protected static string|UnitEnum|null $navigationGroup='Cadastros Gerais'; protected static ?int $navigationSort=60;
 public static function form(Schema $schema): Schema { return $schema->columns(1)->components([Hidden::make('tenant_id')->default(fn()=>Tenant::query()->value('id'))->required(),Section::make('Identificação')->columnSpanFull()->columns(['default'=>1,'md'=>2,'xl'=>4])->schema([
 TextInput::make('code')->label('Código')->required()->columnSpan(['default'=>1,'xl'=>1]), TextInput::make('name')->label('Nome')->required()->columnSpan(['default'=>1,'md'=>1,'xl'=>3]),
 Select::make('category_id')->label('Categoria')->options(fn()=>ProductCategory::query()->where('status','active')->orderBy('name')->pluck('name','id'))->searchable()->preload()->columnSpan(['default'=>1,'md'=>1,'xl'=>2]),
 Select::make('unit_id')->label('Unidade')->options(fn()=>Unit::query()->where('status','active')->orderBy('name')->get()->mapWithKeys(fn(Unit $unit)=>[$unit->id => trim($unit->code.' - '.$unit->name)]))->searchable()->preload()->required(),
 Select::make('brand_id')->label('Marca')->options(fn()=>Brand::query()->where('status','active')->orderBy('name')->pluck('name','id'))->searchable()->preload(),
 Select::make('product_type')->label('Tipo')->options(['material'=>'Material','service'=>'Serviço','fuel'=>'Combustível','part'=>'Peça','epi'=>'EPI','asset'=>'Ativo','input'=>'Insumo'])->default('material')->required(),
 TextInput::make('barcode')->label('Código de barras'), TextInput::make('sku')->label('SKU'), TextInput::make('ncm')->label('NCM'), TextInput::make('cest')->label('CEST'),
 Toggle::make('track_stock')->label('Controla estoque')->default(true), TextInput::make('minimum_stock')->label('Estoque mínimo')->numeric()->default(0), TextInput::make('maximum_stock')->label('Estoque máximo')->numeric(),
 Select::make('status')->label('Status')->options(['active'=>'Ativo','inactive'=>'Inativo'])->default('active')->required(), Textarea::make('description')->label('Descrição')->rows(4)->columnSpanFull()
 ])]); }
 public static function table(Table $table): Table { return $table->columns([TextColumn::make('code')->label('Código')->searchable()->sortable(),TextColumn::make('name')->label('Produto')->searchable()->sortable(),TextColumn::make('product_type')->label('Tipo')->badge(),TextColumn::make('minimum_stock')->label('Estoque mínimo'),IconColumn::make('track_stock')->label('Estoque')->boolean(),TextColumn::make('status')->label('Status')->badge()])->recordActions([EditAction::make()->label('Editar')]); }
 public static function getPages(): array { return ['index'=>ListProducts::route('/'),'create'=>CreateProduct::route('/criar'),'edit'=>EditProduct::route('/{record}/editar')]; }
}

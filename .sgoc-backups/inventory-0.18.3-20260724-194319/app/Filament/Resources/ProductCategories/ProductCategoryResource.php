<?php
 declare(strict_types=1);
 namespace App\Filament\Resources\ProductCategories;
 use App\Modules\Catalog\Domain\Models\ProductCategory;
 use App\Modules\Foundation\Domain\Models\Tenant;
 use App\Filament\Resources\ProductCategories\Pages\CreateProductCategory; use App\Filament\Resources\ProductCategories\Pages\EditProductCategory; use App\Filament\Resources\ProductCategories\Pages\ListProductCategories;
 use BackedEnum; use UnitEnum; use Filament\Resources\Resource; use Filament\Schemas\Schema; use Filament\Schemas\Components\Section;
 use Filament\Forms\Components\Hidden; use Filament\Forms\Components\TextInput; use Filament\Forms\Components\Textarea; use Filament\Forms\Components\Select; use Filament\Forms\Components\Toggle;
 use Filament\Tables\Table; use Filament\Tables\Columns\TextColumn; use Filament\Tables\Columns\IconColumn; use Filament\Actions\EditAction;
 final class ProductCategoryResource extends Resource {
  protected static ?string $model=ProductCategory::class; protected static ?string $recordTitleAttribute='name';
  protected static ?string $modelLabel='categoria de produto'; protected static ?string $pluralModelLabel='categorias de produtos'; protected static ?string $navigationLabel='Categorias de produtos';
  protected static string|BackedEnum|null $navigationIcon='heroicon-o-tag'; protected static string|UnitEnum|null $navigationGroup='Cadastros Gerais'; protected static ?int $navigationSort=50;
  public static function form(Schema $schema): Schema { return $schema->columns(1)->components([Hidden::make('tenant_id')->default(fn()=>Tenant::query()->value('id'))->required(),Section::make('Categoria de produto')->columnSpanFull()->columns(['default'=>1,'md'=>2,'xl'=>4])->schema([
                    TextInput::make('code')->label('Código')->required()->maxLength(180),
                    TextInput::make('name')->label('Nome')->required()->maxLength(180),
                    Textarea::make('description')->label('Descrição')->columnSpanFull(),
                    Select::make('status')->label('Status')->options(['active'=>'Ativo','inactive'=>'Inativo'])->default('active')->required(),
  ])]); }
  public static function table(Table $table): Table { return $table->columns([
            TextColumn::make('code')->label('Código')->searchable()->sortable(),
            TextColumn::make('name')->label('Nome')->searchable()->sortable(),
            TextColumn::make('description')->label('Descrição')->searchable()->sortable(),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn($state)=>$state==='active'?'Ativo':'Inativo')->color(fn($state)=>$state==='active'?'success':'gray'),
  ])->recordActions([EditAction::make()->label('Editar')]); }
  public static function getPages(): array { return ['index'=>ListProductCategories::route('/'),'create'=>CreateProductCategory::route('/criar'),'edit'=>EditProductCategory::route('/{record}/editar')]; }
 }

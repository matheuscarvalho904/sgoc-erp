<?php
 declare(strict_types=1);
 namespace App\Filament\Resources\Units;
 use App\Modules\Catalog\Domain\Models\Unit;
 use App\Modules\Foundation\Domain\Models\Tenant;
 use App\Filament\Resources\Units\Pages\CreateUnit; use App\Filament\Resources\Units\Pages\EditUnit; use App\Filament\Resources\Units\Pages\ListUnits;
 use BackedEnum; use UnitEnum; use Filament\Resources\Resource; use Filament\Schemas\Schema; use Filament\Schemas\Components\Section;
 use Filament\Forms\Components\Hidden; use Filament\Forms\Components\TextInput; use Filament\Forms\Components\Textarea; use Filament\Forms\Components\Select; use Filament\Forms\Components\Toggle;
 use Filament\Tables\Table; use Filament\Tables\Columns\TextColumn; use Filament\Tables\Columns\IconColumn; use Filament\Actions\EditAction;
 final class UnitResource extends Resource {
  protected static ?string $model=Unit::class; protected static ?string $recordTitleAttribute='name';
  protected static ?string $modelLabel='unidade'; protected static ?string $pluralModelLabel='unidades de medida'; protected static ?string $navigationLabel='Unidades de medida';
  protected static string|BackedEnum|null $navigationIcon='heroicon-o-scale'; protected static string|UnitEnum|null $navigationGroup='Cadastros Gerais'; protected static ?int $navigationSort=10;
  public static function form(Schema $schema): Schema { return $schema->columns(1)->components([Hidden::make('tenant_id')->default(fn()=>Tenant::query()->value('id'))->required(),Section::make('Unidade')->columnSpanFull()->columns(['default'=>1,'md'=>2,'xl'=>4])->schema([
                    TextInput::make('code')->label('Código')->required()->maxLength(180),
                    TextInput::make('name')->label('Nome')->required()->maxLength(180),
                    TextInput::make('symbol')->label('Símbolo')->required()->maxLength(180),
                    TextInput::make('decimal_places')->label('Casas decimais')->numeric()->required(),
                    Select::make('status')->label('Status')->options(['active'=>'Ativo','inactive'=>'Inativo'])->default('active')->required(),
  ])]); }
  public static function table(Table $table): Table { return $table->columns([
            TextColumn::make('code')->label('Código')->searchable()->sortable(),
            TextColumn::make('name')->label('Nome')->searchable()->sortable(),
            TextColumn::make('symbol')->label('Símbolo')->searchable()->sortable(),
            TextColumn::make('decimal_places')->label('Casas decimais')->searchable()->sortable(),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn($state)=>$state==='active'?'Ativo':'Inativo')->color(fn($state)=>$state==='active'?'success':'gray'),
  ])->recordActions([EditAction::make()->label('Editar')]); }
  public static function getPages(): array { return ['index'=>ListUnits::route('/'),'create'=>CreateUnit::route('/criar'),'edit'=>EditUnit::route('/{record}/editar')]; }
 }
